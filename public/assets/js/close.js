close = document.querySelector(".close");
notifButton = document.querySelector(".notifications");
notifPopup = document.querySelector(".gest-notif");

console.log(close);
console.log(notifButton);
console.log(notifPopup);

notifButton.addEventListener("click", OpenNotif);

function OpenNotif() {
  notifPopup.style.display = "flex";
  notifPopup.style.opacity = "1";
}

close.addEventListener("click", CloseNotif);

function CloseNotif() {
  notifPopup.style.display = "none";
  notifPopup.style.opacity = "0";
}
