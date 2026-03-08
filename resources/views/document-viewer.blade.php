<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=3,user-scalable=yes">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#404040;overflow-x:hidden;font-family:sans-serif}
#viewer{padding:8px 0}
canvas{display:block;margin:6px auto;background:#fff;box-shadow:0 1px 6px rgba(0,0,0,.35)}
.msg{color:#ccc;text-align:center;padding:48px 16px;font:14px/1.5 sans-serif}
.err{color:#ff6b6b}

/* DOCX print-view page styling */
#viewer .docx-wrapper{background:transparent!important;padding:16px 0!important;overflow-x:hidden!important}
#viewer .docx-wrapper>section.docx{
  width:calc(100% - 16px)!important;min-width:0!important;max-width:794px!important;
  min-height:auto!important;
  padding:48px 32px!important;margin:0 auto 24px auto!important;
  background:#fff!important;border-radius:2px!important;
  box-shadow:0 2px 8px rgba(0,0,0,.4)!important;
  overflow-wrap:break-word;word-break:break-word;
  position:relative;
}
#viewer .docx-wrapper>section.docx::after{
  content:attr(data-page);position:absolute;bottom:8px;right:12px;
  font:11px/1 sans-serif;color:#999;
}
#viewer .docx-wrapper>section.docx *{max-width:100%!important}
#viewer table{width:100%!important;table-layout:fixed}
#viewer img{max-width:100%!important;height:auto!important}

/* TXT styling */
pre#viewer{background:#fff;margin:8px;padding:16px;border-radius:6px;font:13px/1.6 monospace;white-space:pre-wrap;word-break:break-word;min-height:80vh}

/* Watermark overlay */
#watermark{
  position:fixed;top:0;left:0;width:100%;height:100%;
  background-image:url('/img/legatura_watermark.png');
  background-size:40%;background-position:center;background-repeat:repeat;
  opacity:0.2;pointer-events:none;z-index:9999;
}
</style>
</head>
<body>
<div id="watermark"></div>
<div id="viewer"><p class="msg">Loading document…</p></div>

<script>
  // Disable right-click
  document.addEventListener('contextmenu', function(e){ e.preventDefault(); return false; });
  // Disable save/print/copy shortcuts
  document.addEventListener('keydown', function(e){
    if ((e.ctrlKey||e.metaKey) && ['s','p','c','a','u'].indexOf(e.key)>=0){ e.preventDefault(); return false; }
    if (e.key==='F12'||((e.ctrlKey||e.metaKey)&&e.shiftKey&&e.key==='I')){ e.preventDefault(); return false; }
  });
</script>

<script>
var FILE_URL = @json($fileUrl);
var FILE_EXT = @json($ext);

function renderPdf() {
  var s1 = document.createElement('script');
  s1.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
  s1.onload = function() {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    var viewer = document.getElementById('viewer');
    var dpr = window.devicePixelRatio || 1;
    pdfjsLib.getDocument(FILE_URL).promise.then(function(pdf) {
      viewer.innerHTML = '';
      var cssW = window.innerWidth - 16;
      var renderPage = function(num) {
        if (num > pdf.numPages) return;
        pdf.getPage(num).then(function(page) {
          var uv = page.getViewport({scale: 1});
          var cssScale = cssW / uv.width;
          var renderScale = cssScale * dpr;
          var vp = page.getViewport({scale: renderScale});
          var c = document.createElement('canvas');
          c.width = vp.width; c.height = vp.height;
          c.style.width = Math.floor(vp.width / dpr) + 'px';
          c.style.height = Math.floor(vp.height / dpr) + 'px';
          viewer.appendChild(c);
          page.render({canvasContext: c.getContext('2d'), viewport: vp}).promise.then(function() {
            renderPage(num + 1);
          });
        });
      };
      renderPage(1);
    }).catch(function(e) {
      viewer.innerHTML = '<p class="msg err">Unable to load PDF: ' + e.message + '</p>';
    });
  };
  document.head.appendChild(s1);
}

function renderDocx() {
  var viewer = document.getElementById('viewer');
  // Load JSZip first, then docx-preview
  var s1 = document.createElement('script');
  s1.src = 'https://unpkg.com/jszip@3.10.1/dist/jszip.min.js';
  s1.onload = function() {
    var s2 = document.createElement('script');
    s2.src = 'https://unpkg.com/docx-preview@0.3.3/dist/docx-preview.min.js';
    s2.onload = function() {
      viewer.innerHTML = '<p class="msg">Fetching document…</p>';
      var xhr = new XMLHttpRequest();
      xhr.open('GET', FILE_URL, true);
      xhr.responseType = 'arraybuffer';
      xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
          var blob = new Blob([xhr.response], {type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'});
          viewer.innerHTML = '<p class="msg">Rendering…</p>';
          docx.renderAsync(blob, viewer, null, {
            className: 'docx',
            inWrapper: true,
            ignoreWidth: true,
            ignoreHeight: false,
            ignoreFonts: false,
            breakPages: true
          }).then(function() {
            viewer.style.padding = '0';

            // Manually paginate if library outputs only 1 section
            var wrapper = viewer.querySelector('.docx-wrapper');
            var sections = wrapper ? wrapper.querySelectorAll('section.docx') : [];

            if (sections.length === 1) {
              var section = sections[0];
              var children = Array.prototype.slice.call(section.children);
              if (children.length > 1) {
                // Calculate page content height based on A4 ratio
                var sectionW = section.offsetWidth;
                var pageH = Math.round(sectionW * 1.414);
                var padV = 96; // 48px top + 48px bottom padding from CSS
                var PAGE_H = Math.max(400, pageH - padV);

                // Measure child heights
                var heights = children.map(function(c){ return c.offsetHeight; });

                // Group children into pages
                var groups = [[]];
                var used = 0;
                for (var k = 0; k < children.length; k++) {
                  if (used + heights[k] > PAGE_H && groups[groups.length-1].length > 0) {
                    groups.push([]);
                    used = 0;
                  }
                  groups[groups.length-1].push(children[k]);
                  used += heights[k];
                }

                if (groups.length > 1) {
                  var cls = section.className;
                  var css = section.style.cssText;
                  for (var g = 0; g < groups.length; g++) {
                    var ns = document.createElement('section');
                    ns.className = cls;
                    ns.style.cssText = css;
                    for (var m = 0; m < groups[g].length; m++) {
                      ns.appendChild(groups[g][m]);
                    }
                    wrapper.insertBefore(ns, section);
                  }
                  wrapper.removeChild(section);
                }
              }
            }

            // Add page numbers
            var allPages = viewer.querySelectorAll('.docx-wrapper > section.docx');
            for (var i = 0; i < allPages.length; i++) {
              allPages[i].setAttribute('data-page', 'Page ' + (i + 1) + ' of ' + allPages.length);
            }
          }).catch(function(e) {
            viewer.innerHTML = '<p class="msg err">Render error: ' + e.message + '</p>';
          });
        } else {
          viewer.innerHTML = '<p class="msg err">Server returned ' + xhr.status + '</p>';
        }
      };
      xhr.onerror = function() {
        viewer.innerHTML = '<p class="msg err">Network error loading document</p>';
      };
      xhr.send();
    };
    s2.onerror = function() { viewer.innerHTML = '<p class="msg err">Failed to load document renderer</p>'; };
    document.head.appendChild(s2);
  };
  s1.onerror = function() { viewer.innerHTML = '<p class="msg err">Failed to load document library</p>'; };
  document.head.appendChild(s1);
}

function renderText() {
  var viewer = document.getElementById('viewer');
  // Replace div with pre for plain text
  var pre = document.createElement('pre');
  pre.id = 'viewer';
  pre.innerHTML = '<span class="msg">Loading…</span>';
  viewer.parentNode.replaceChild(pre, viewer);
  var xhr = new XMLHttpRequest();
  xhr.open('GET', FILE_URL, true);
  xhr.onload = function() {
    if (xhr.status >= 200 && xhr.status < 300) {
      document.getElementById('viewer').textContent = xhr.responseText;
    } else {
      document.getElementById('viewer').innerHTML = '<span class="err">Server returned ' + xhr.status + '</span>';
    }
  };
  xhr.onerror = function() {
    document.getElementById('viewer').innerHTML = '<span class="err">Network error</span>';
  };
  xhr.send();
}

function renderUnsupported() {
  document.getElementById('viewer').innerHTML =
    '<p class="msg" style="font-size:40px;margin-bottom:12px">📄</p>' +
    '<p class="msg"><strong>.' + FILE_EXT.toUpperCase() + ' format</strong></p>' +
    '<p class="msg" style="opacity:.7">Preview not available for this file type.</p>';
}

// Route to correct renderer
if (FILE_EXT === 'pdf') {
  renderPdf();
} else if (FILE_EXT === 'docx') {
  renderDocx();
} else if (['txt', 'csv', 'rtf'].indexOf(FILE_EXT) >= 0) {
  renderText();
} else if (FILE_EXT === 'doc') {
  document.getElementById('viewer').innerHTML =
    '<p class="msg" style="font-size:40px;margin-bottom:12px">📄</p>' +
    '<p class="msg"><strong>.DOC format</strong></p>' +
    '<p class="msg" style="opacity:.7">Legacy .doc files cannot be previewed inline.<br>Please ask the uploader for a .docx or .pdf version.</p>';
} else {
  renderUnsupported();
}
</script>
</body>
</html>
