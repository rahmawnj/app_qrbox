<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Generate PDF Example</title>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
    }
    .content {
      border: 1px solid #333;
      padding: 20px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

  <div class="content" id="content-to-pdf">
    <h1>Konten yang Akan Disimpan ke PDF</h1>
    <p>Ini adalah contoh konten yang akan dimasukkan ke file PDF.</p>
  </div>

  <button id="btn-generate-pdf">Simpan ke PDF</button>

  <script>
    const { jsPDF } = window.jspdf;

    document.getElementById('btn-generate-pdf').addEventListener('click', () => {
      const doc = new jsPDF();

      // Ambil isi elemen
      const content = document.getElementById('content-to-pdf').innerText;

      // Tambahkan teks ke PDF (x, y, text)
      doc.text(content, 10, 10);

      // Simpan file PDF dengan nama 'file.pdf'
      doc.save('file.pdf');
    });
  </script>

</body>
</html>
