<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - Contractor</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f4f4f4;
    }

    form {
        max-width: 500px; 
        margin: 0 auto;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    form > div {
        margin-bottom: 20px;
        padding: 0;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
    }

    input[type="text"],
    textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box; 
    }

    textarea {
        resize: vertical;
    }

    input[type="file"] {
        margin-top: 5px;
        margin-bottom: 5px;
    }

    .file-hint {
        margin-top: 0;
        font-size: 0.9em;
        color: #666;
        margin-bottom: 10px;
    }

    span {
        color: red;
        font-size: 0.9em;
        display: block;
        margin-top: -5px; 
    }

    .success-message {
        color: green;
        padding: 10px;
        border: 1px solid green;
        background-color: #e6ffe6;
        margin-bottom: 15px;
        border-radius: 4px;
    }

    .error-list {
        color: red;
        padding: 10px;
        border: 1px solid red;
        background-color: #ffe6e6;
        margin-bottom: 15px;
        border-radius: 4px;
    }

    .error-list ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .button-group {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #eee;
    }

    .cancel-button {
        padding: 10px 20px;
        background-color: #ccc;
        color: #333;
        text-decoration: none;
        border-radius: 4px;
        display: inline-block;
        font-size: 16px;
        transition: background-color 0.2s;
    }

    .cancel-button:hover {
        background-color: #bbb;
    }

    .post-button {
        padding: 10px 20px;
        background-color: #ff6b00;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.2s;
    }

    .post-button:hover {
        background-color: #e65c00;
    }
    </style>
</head>
<body>
    <form action="/contractor/bids" method="POST" enctype="multipart/form-data">
        @csrf

        <div>
            <label for="project_title">Header</label>
            <input type="text" id="project_title" name="project_title" placeholder="Enter header/title" required>
            @error('project_title')
                <span>{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="project_description">Description</label>
            <textarea id="project_description" name="project_description" placeholder="Enter description" rows="6" required></textarea>
            @error('project_description')
                <span>{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="project_location">Location</label>
            <input type="text" id="project_location" name="project_location" placeholder="Enter project location (e.g., City, Province)" required>
            @error('project_location')
                <span>{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label>Photo/Video</label>
            <div>
                <input type="file" name="media[]" accept="image/*,video/*" multiple="multiple" id="media_input">
                <p class="file-hint">You can upload photos or videos. Hold Ctrl (or Cmd on Mac) to select multiple files.</p>
                <p id="media_count" style="font-size: 0.85em; color: #5cb85c; margin-top: 5px; display: none;"></p>
                <div id="media_list" style="margin-top: 10px; display: none;"></div>
                @error('media')
                    <span>{{ $message }}</span>
                @enderror
                @error('media.*')
                    <span>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <input type="hidden" name="contractor_id" value="1"> @if(session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="error-list">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="button-group">
            <a href="/" class="cancel-button">Cancel</a>
            <button type="submit" class="post-button">Post</button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mediaInput = document.getElementById('media_input');
            const mediaCount = document.getElementById('media_count');
            const mediaList = document.getElementById('media_list');
            
       
            let selectedMedia = [];
            
           
            function updateMediaDisplay() {
                const fileCount = selectedMedia.length;
                if (fileCount > 0) {
                    mediaCount.style.display = 'block';
                    mediaCount.textContent = `${fileCount} file(s) selected`;
                    
                    
                    mediaList.style.display = 'block';
                    mediaList.innerHTML = '<div style="font-weight: bold; margin-bottom: 5px; color: #333;">Selected files:</div>' +
                        selectedMedia.map((file, index) => 
                            `<div style="padding: 5px; margin: 3px 0; background-color: #f0f0f0; border-radius: 3px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.9em;">${file.name}</span>
                                <button type="button" onclick="removeMedia(${index})" style="background-color: #dc3545; color: white; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer; font-size: 0.85em;">Remove</button>
                            </div>`
                        ).join('');
                } else {
                    mediaCount.style.display = 'none';
                    mediaList.style.display = 'none';
                }
            }
            
          
            mediaInput.addEventListener('change', function() {
                const newFiles = Array.from(this.files);
                
               
                newFiles.forEach(newFile => {
                    const exists = selectedMedia.some(existingFile => 
                        existingFile.name === newFile.name && existingFile.size === newFile.size
                    );
                    if (!exists) {
                        selectedMedia.push(newFile);
                    }
                });
                
                
                const dataTransfer = new DataTransfer();
                selectedMedia.forEach(file => dataTransfer.items.add(file));
                this.files = dataTransfer.files;
                
                updateMediaDisplay();
            });
            
          
            window.removeMedia = function(index) {
                selectedMedia.splice(index, 1);
                const dataTransfer = new DataTransfer();
                selectedMedia.forEach(file => dataTransfer.items.add(file));
                mediaInput.files = dataTransfer.files;
                updateMediaDisplay();
            };
        });
    </script>
</body>
</html>