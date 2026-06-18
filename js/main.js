/* ==========================================================================
   OLGA ASTRO — main.js
   Progressive enhancement only. All content is in the HTML; JS reveals/animates.
   ========================================================================== */
(function () {
  "use strict";

  // Signal that JS is on (CSS uses .no-js fallbacks for accordion/reveal).
  document.documentElement.classList.remove("no-js");

  document.addEventListener("DOMContentLoaded", function () {
    /* --- Copyright year ------------------------------------------------- */
    var yearEl = document.getElementById("year");
    if (yearEl) yearEl.textContent = new Date().getFullYear();

    /* --- Sticky header state ------------------------------------------- */
    var header = document.querySelector(".site-header");
    if (header) {
      var onScroll = function () {
        header.classList.toggle("is-stuck", window.scrollY > 24);
      };
      onScroll();
      window.addEventListener("scroll", onScroll, { passive: true });
    }

    /* --- Mobile navigation drawer -------------------------------------- */
    var toggle = document.querySelector(".nav-toggle");
    var drawer = document.getElementById("mobile-nav");
    if (toggle && drawer) {
      var setNav = function (open) {
        toggle.setAttribute("aria-expanded", String(open));
        drawer.classList.toggle("is-open", open);
        document.body.classList.toggle("nav-open", open);
      };
      toggle.addEventListener("click", function () {
        setNav(toggle.getAttribute("aria-expanded") !== "true");
      });
      drawer.addEventListener("click", function (e) {
        if (e.target.closest("a")) setNav(false);
      });
      document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") setNav(false);
      });
    }

    /* --- Accordion (content already in DOM; we only toggle visibility) -- */
    var triggers = document.querySelectorAll(".accordion__trigger");
    triggers.forEach(function (btn) {
      var panel = document.getElementById(btn.getAttribute("aria-controls"));
      if (!panel) return;
      btn.addEventListener("click", function () {
        var open = btn.getAttribute("aria-expanded") === "true";
        btn.setAttribute("aria-expanded", String(!open));
        panel.hidden = false; // keep crawlable & focusable
        panel.style.maxHeight = open ? "0px" : panel.scrollHeight + "px";
      });
    });
    // Recompute open panels on resize so reflowed text isn't clipped.
    window.addEventListener("resize", function () {
      document.querySelectorAll('.accordion__trigger[aria-expanded="true"]').forEach(function (btn) {
        var panel = document.getElementById(btn.getAttribute("aria-controls"));
        if (panel) panel.style.maxHeight = panel.scrollHeight + "px";
      });
    });

    /* --- Reveal on scroll ---------------------------------------------- */
    var revealEls = document.querySelectorAll(".reveal");
    if ("IntersectionObserver" in window && revealEls.length) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            io.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });
      revealEls.forEach(function (el) { io.observe(el); });
    } else {
      revealEls.forEach(function (el) { el.classList.add("is-visible"); });
    }
  });
})();
