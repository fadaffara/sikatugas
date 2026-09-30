let deleteId = null;
function showConfirmDelete(id) {
    deleteId = id; 
    document.getElementById('confirmModal').style.display = 'block'; 
}
function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none'; 
}
function deleteData() {
    if (deleteId) {
        window.location.href = 'manager-delete-tasks.php?id=' + deleteId;
    }
}
  function openDetailModal(taskId) {
    $.ajax({
        url: 'manager-fetch-task-details.php',
        type: 'GET',
        data: { id: taskId },
        success: function(data) {
            $('#taskDetailContent').html(data);
            document.getElementById('taskDetailModal').style.display = 'block';
        }
    });
}
function closeTasksDetailsModal() {
    document.getElementById('taskDetailModal').style.display = 'none';
}
window.onclick = function(event) {
    if (event.target == document.getElementById('taskDetailModal')) {
        closeTasksDetailsModal();
    }
};
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
    fetch('admin-notification.php')
      .then(response => {
        if (!response.ok) throw new Error('Failed to fetch notifications');
        return response.json();
      })
      .then(notifications => {
        const notificationContainer = document.getElementById('notification-container');
        notificationContainer.innerHTML = ''; 
        if (notifications.length > 0) {
          notifications.forEach(notification => {
            const { pesan, tipe } = notification;
            const notificationItem = document.createElement('div');
            notificationItem.classList.add('notification-item', tipe.toLowerCase());
            notificationItem.innerHTML = `<div>${pesan}</div>`;
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
function getMonthName(monthIndex) {
  const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  return months[monthIndex];
}

























































