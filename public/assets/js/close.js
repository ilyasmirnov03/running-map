close = document.querySelector(".close");
notifButton = document.querySelector(".notifications");
notifPopup = document.querySelector(".gest-notif");
bg = document.querySelector(".notif-bg");

notifButton.addEventListener("click", OpenNotif);

function OpenNotif() {
  notifPopup.style.display = "flex";
  notifPopup.style.opacity = "1";
  bg.style.display = "block";
}

close.addEventListener("click", CloseNotif);

function CloseNotif() {
  notifPopup.style.display = "none";
  notifPopup.style.opacity = "0";
  bg.style.display = "none";
}

bg.addEventListener("click", CloseNotif);
