/* Category chips: filter category cards and load matching providers via AJAX. */
document.addEventListener("DOMContentLoaded", () => {
  const box = document.getElementById("svcBrowser");
  if (!box) return;
  const A = window.AASRA,
    esc = A.esc;
  const cards = box.querySelectorAll("[data-svc]"),
    chips = box.querySelectorAll(".chip");
  const wrap = document.getElementById("svcProviders"),
    grid = document.getElementById("svcProvidersGrid");
  const title = document.getElementById("svcProvidersTitle"),
    all = document.getElementById("svcProvidersAll");
  const stars = (r) => {
    let h = "";
    for (let i = 1; i <= 5; i++)
      h +=
        '<i class="bi bi-star' +
        (r >= i ? "-fill" : r >= i - 0.5 ? "-half" : "") +
        '"></i>';
    return '<span class="stars">' + h + "</span>";
  };

  chips.forEach((c) =>
    c.addEventListener("click", async () => {
      chips.forEach((x) => x.classList.toggle("active", x === c));
      const id = c.dataset.category;
      cards.forEach((x) => (x.hidden = id !== "0" && x.dataset.svc !== id));
      if (id === "0") {
        wrap.hidden = true;
        return;
      }
      wrap.hidden = false;
      grid.innerHTML =
        '<div class="col-12 text-secondary">Loading providers…</div>';
      title.textContent = "Providers for " + c.textContent;
      // Public area: clean /categories/<id> URL; user area: provider search filtered by category.
      all.href =
        box.dataset.area === "user"
          ? box.dataset.listing + "?category=" + id
          : box.dataset.listing + "/" + id;
      try {
        const res = await fetch(
          A.url("api/providers?category=" + encodeURIComponent(id) + "&limit=6"),
          { headers: { Accept: "application/json" } },
        );
        const list = (await res.json()).providers || [];
        grid.innerHTML = list.length
          ? list
              .map(
                (p) => `
        <div class="col-md-6 col-lg-4"><div class="prov-card h-100">
          <div class="d-flex gap-3 align-items-center">${p.photo ? `<img class="avatar avatar-lg" src="${esc(p.photo)}" alt="">` : `<span class="avatar avatar-lg">${esc(p.initial)}</span>`}
            <div class="min-w-0"><h3 class="h6 mb-1 text-truncate"><a class="stretched-link text-reset text-decoration-none" href="${esc(box.dataset.profile + p.id)}">${esc(p.name)}</a></h3>
            <div class="small">${stars(p.rating)} <span class="text-secondary">${p.reviews ? p.rating.toFixed(1) + " (" + p.reviews + ")" : "New"}</span></div></div></div>
          <p class="text-secondary small mt-3 mb-2 clamp-2">${esc(p.bio)}</p>
          <div class="small text-secondary"><i class="bi bi-geo-alt"></i> ${esc(p.location)}${p.price_from ? " · From PKR " + Number(p.price_from).toLocaleString() + "/visit" : ""}</div>
        </div></div>`,
              )
              .join("")
          : '<div class="col-12"><div class="empty-state card-soft"><i class="bi bi-search"></i><p class="mb-0 mt-2">No verified providers offer this category yet.</p></div></div>';
      } catch (e) {
        grid.innerHTML =
          '<div class="col-12 text-danger">Could not load providers. Please try again.</div>';
      }
    }),
  );
});
