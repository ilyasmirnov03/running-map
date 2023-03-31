cross = document.querySelector(".croix");
primaryMap = document.querySelector(".primaryMap");
map = document.getElementById("map");

primaryMap.addEventListener("click", function () {
  primaryMap.classList.add("active");
  cross.style.display = "block";
  cross.style.opacity = "1";
});
cross.addEventListener("click", function () {
  primaryMap.classList.remove("active");
  cross.style.display = "none";
  cross.style.opacity = "0";
});
console.log(cross);
console.log(map);
console.log(primaryMap);
