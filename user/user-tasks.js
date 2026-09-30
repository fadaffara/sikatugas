function openDetailModal(taskId) {
  $.ajax({
      url: 'user-fetch-task-details.php',
      type: 'GET',
      data: { id: taskId },
      success: function(data) {
          $('#taskDetailContent').html(data);  
          document.getElementById('taskDetailModal').style.display = 'block';  
      },
      error: function() {
          alert('Gagal memuat detail tugas');
      }
  });
}

function openUploadModal(taskId) {
  document.getElementById('task_id').value = taskId; 
  document.getElementById('uploadFileModal').style.display = 'block';  
}

function closeModal() {
  document.getElementById('taskDetailModal').style.display = 'none';
  document.getElementById('uploadFileModal').style.display = 'none';
}

window.onclick = function(event) {
  if (event.target == document.getElementById('taskDetailModal') || 
      event.target == document.getElementById('uploadFileModal')) {
      closeModal();
  }
};

function downloadFile(fileName) {
  window.location.href = 'user-download.php?file=' + encodeURIComponent(fileName);
}

function toggleDetails() {
  var details = document.querySelector('.profile-details');
  var profileImg = document.querySelector('.profile-img');

  const notificationPopup = document.getElementById('notification-popup');
  notificationPopup.style.display = 'none';

  if (details.style.display === 'none' || details.style.display === '') {
      details.style.display = 'block';
      document.addEventListener('click', hideDetailsOnClickOutside);
  } else {
      details.style.display = 'none';
      document.removeEventListener('click', hideDetailsOnClickOutside);
  }

  function hideDetailsOnClickOutside(event) {
      if (!details.contains(event.target) && event.target !== profileImg) {
          details.style.display = 'none';
          document.removeEventListener('click', hideDetailsOnClickOutside);
      }
  }
}


document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('filterModal');
  const openModalBtn = document.getElementById('openFilterModal');
  const closeModalBtn = document.querySelector('.close');
  const cancelBtn = document.getElementById('cancelFilter');

  openModalBtn.addEventListener('click', () => {
    modal.style.display = 'block';
  });

  closeModalBtn.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  cancelBtn.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  window.addEventListener('click', (event) => {
    if (event.target === modal) {
      modal.style.display = 'none'; 
    }
  });
});

// Function untuk menampilkan notifikasi
function showNotification(message) {
  const notificationPopup = document.getElementById('notification-popup');
  const notificationMessage = document.getElementById('notification-message');
  notificationMessage.textContent = message;
  notificationPopup.style.display = 'block';
}

function closeNotification() {
  const notificationPopup = document.getElementById('notification-popup');
  notificationPopup.style.display = 'none';
}



document.querySelector('.notification').addEventListener('click', function () {
  const notificationPopup = document.getElementById('notification-popup');

  if (notificationPopup.style.display === 'none' || notificationPopup.style.display === '') {
    fetch('user-notification.php')
      .then(response => {
        if (!response.ok) throw new Error('Failed to fetch tasks');
        return response.json();
      })
      .then(tasks => {
        const notificationContainer = document.getElementById('notification-container');
        notificationContainer.innerHTML = ''; // Bersihkan kontainer

        if (tasks.length > 0) {
          tasks.forEach(task => {
            const { message, tipe } = task;

            const notificationItem = document.createElement('div');
            notificationItem.classList.add('notification-item', tipe.toLowerCase());
            notificationItem.innerHTML = `<div>${message}</div>`;

            notificationContainer.appendChild(notificationItem);
          });
        } else {
          notificationContainer.innerHTML = "<div class='notification-item'>Tidak ada notifikasi apapun</div>";
        }

        notificationPopup.style.display = 'block';
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Gagal memuat notifikasi.');
      });
  } else {
    closeNotification();
  }
});




// Fungsi untuk mendapatkan nama bulan
function getMonthName(monthIndex) {
  const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  return months[monthIndex];
}







$(document).ready(function() {
  $('td.prioritas').each(function() {
    var prioritas = $(this).text().trim();
    var colors = {
      'Rendah': {'bg': '#cce7ff', 'color': '#003366'},
      'Sedang': {'bg': '#fff9c4', 'color': '#b28900'},
      'Tinggi': {'bg': '#ff7043', 'color': 'white'}
    };
    var color = colors[prioritas];
    if (color) {
      $(this).css({
        'background-color': color.bg,
        'color': color.color,
        'font-weight': 'bold',
        'padding': '5px 10px',
        'transition': 'background-color 0.3s ease'
      });
    }
  });


  $('td.status').each(function() {
    var status = $(this).text().trim();
    var statusColors = {
      'Menunggu Pengumpulan': {'bg': '#80d0ff', 'color': '#0066cc'},
      'Menunggu Approval': {'bg': '#ffcc80', 'color': '#ff6600'},
      'Butuh Revisi': {'bg': '#ffee58', 'color': '#f57f17'},
      'Selesai': {'bg': '#81c784', 'color': 'white'}
    };
    var statusColor = statusColors[status];
    if (statusColor) {
      $(this).css({
        'background-color': statusColor.bg,
        'color': statusColor.color,
        'font-weight': 'bold',
        'padding': '5px 10px',
        'transition': 'background-color 0.3s ease'
      });
    }
  });


  var today = new Date();
  today.setHours(0, 0, 0, 0);
  $('table tbody tr').each(function() {
    var deadlineText = $(this).find('.deadline').text().trim();
    var deadlineParts = deadlineText.split('-'); 


    var deadlineDate = new Date(deadlineParts[2], deadlineParts[1] - 1, deadlineParts[0]);

    if (deadlineDate.getTime() === today.getTime()) {
      $(this).find('.deadline').addClass('deadline-today');
    } else if (deadlineDate.getTime() < today.getTime()) {
      $(this).find('.deadline').addClass('deadline-passed');
    } else {
      $(this).find('.deadline').addClass('deadline-upcoming');
    }
  });
});


$('table tbody tr').each(function() {
  $(this).find('td:nth-child(3)').css({
    'background-color': '#e6e6fa',
    'color': '#4b0082',
    'font-weight': 'bold',
    'padding': '5px 10px',
    'transition': 'background-color 0.3s ease'
  });
});