

function toggleDetails() {
  var details = document.querySelector('.profile-details');
  var profileImg = document.querySelector('.profile-img');

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