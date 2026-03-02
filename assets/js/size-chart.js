document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("sizeChartModal");
  const btn = document.getElementById("openSizeChart");
  const closeBtn = document.querySelector(".close");
  if (btn && modal) {
    btn.onclick = () => (modal.style.display = "flex");
    closeBtn.onclick = () => (modal.style.display = "none");
    window.onclick = (e) => {
      if (e.target === modal) modal.style.display = "none";
    };
  }
});
