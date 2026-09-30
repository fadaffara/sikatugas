document.addEventListener('DOMContentLoaded', function () {
  const fileInput = document.getElementById('photo');
  const fileNameElement = document.getElementById('file-name');
  fileInput.addEventListener('change', function () {
    const file = this.files[0]; // Ambil file yang dipilih
    if (file) {
      fileNameElement.textContent = file.name; // Menampilkan nama file
      fileNameElement.classList.remove('default'); // Menghapus kelas default
    } else {
      fileNameElement.textContent = 'Belum dipilih'; // Jika tidak ada file yang dipilih
      fileNameElement.classList.add('default'); // Menambahkan kelas default
    }
  });
});