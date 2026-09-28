document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("sizeChartModal");
  const open = document.getElementById("openSizeChart");
  const close = modal?.querySelector(".close");
  if (!modal || !open || !close) return;
  let previousFocus = null;
  const show = () => { previousFocus = document.activeElement; modal.hidden = false; modal.style.display = "flex"; close.focus(); };
  const hide = () => { modal.hidden = true; modal.style.display = ""; if (previousFocus?.focus) previousFocus.focus(); };
  open.addEventListener("click", show);
  close.addEventListener("click", hide);
  modal.addEventListener("click", e => { if (e.target === modal) hide(); });
  document.addEventListener("keydown", e => { if (e.key === "Escape" && !modal.hidden) hide(); });
});
