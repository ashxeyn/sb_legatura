from pypdf import PdfReader
pdf_path = r'c:\xampp\htdocs\sb_legatura\database\CARL-WAYNE-WORK.pdf'
reader = PdfReader(pdf_path)
print('PAGES', len(reader.pages))
for i, pg in enumerate(reader.pages, start=1):
    text = (pg.extract_text() or '').strip()
    print(f'--- PAGE {i} ---')
    print(text[:5000])
    print('')
