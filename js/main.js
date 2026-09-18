/* ==========================================================================
   OLGA ASTRO — main.js
   Progressive enhancement only. All content is in the HTML; JS reveals/animates.
   ========================================================================== */
(function () {
  "use strict";

  // Signal that JS is on (CSS uses .no-js fallbacks for accordion/reveal).
  document.documentElement.classList.remove("no-js");

  document.addEventListener("DOMContentLoaded", function () {
    /* --- Contact form: AJAX submit to send.php -------------------------
       Same pattern as the studio's other sites. Without JS the form still
       posts normally to send.php, which returns JSON; that is ugly but not
       broken, and the contact page also lists the email and messengers. */
    var reqForm = document.getElementById("request-form");
    if (reqForm) {
      var fb = document.getElementById("form-feedback");
      var submitBtn = document.getElementById("form-submit");
      var FB = {
        ru: { sending: "Отправляем\u2026", offline: "Не удалось отправить. Проверьте соединение или напишите на info@olga-astro.com." },
        en: { sending: "Sending\u2026",     offline: "The message could not be sent. Check your connection, or write to info@olga-astro.com." },
        fr: { sending: "Envoi en cours\u2026", offline: "Envoi impossible. Vérifiez votre connexion, ou écrivez à info@olga-astro.com." }
      };
      var fbT = FB[document.documentElement.lang] || FB.en;

      var say = function (text, ok) {
        fb.textContent = text;
        fb.className = "form__feedback " + (ok ? "is-success" : "is-error");
        fb.hidden = false;
      };

      reqForm.addEventListener("submit", function (ev) {
        ev.preventDefault();
        if (!reqForm.checkValidity()) { reqForm.reportValidity(); return; }

        var original = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = fbT.sending;
        fb.hidden = true;

        fetch(reqForm.action, {
          method: "POST",
          body: new FormData(reqForm),
          headers: { Accept: "application/json" }
        })
          .then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (json) {
              return { ok: res.ok, json: json };
            });
          })
          .then(function (r) {
            if (r.ok && r.json.ok) {
              say(r.json.message || "OK", true);
              reqForm.reset();
            } else {
              say(r.json.error || fbT.offline, false);
            }
          })
          .catch(function () { say(fbT.offline, false); })
          .finally(function () {
            submitBtn.disabled = false;
            submitBtn.textContent = original;
          });
      });
    }

    /* --- Cookie consent banner ------------------------------------------
       Built here rather than duplicated into 51 static pages. The wording is
       taken from the previous WordPress site, in the visitor's language.

       IMPORTANT: this site currently sets NO cookies and loads NO analytics.
       The banner records the choice and nothing else. When analytics are
       added, load them from window.olgaConsent.onAccept() and nowhere else,
       so that refusing actually means something. Until then the banner is
       informational, which is why "accept" is not preselected anywhere. */
    var CONSENT_KEY = "olga-consent";
    var CONSENT_DAYS = 90; // durée annoncée dans la politique de confidentialité

    var T = {
      ru: { text: "Мы используем файлы cookie, необходимые для работы сайта, и анонимную статистику посещений. Вы можете принять или отклонить их. Отказ не влияет на работу сайта.",
            accept: "Принимать", decline: "Отклонить", more: "Политика конфиденциальности",
            href: "politika-konfidentsialnosti/", label: "Управление согласием" },
      en: { text: "We use cookies that are necessary for the site to work, plus anonymous visit statistics. You can accept or decline them. Declining does not affect how the site works.",
            accept: "Accept", decline: "Decline", more: "Privacy policy",
            href: "privacy-policy/", label: "Manage consent" },
      fr: { text: "Nous utilisons des cookies nécessaires au fonctionnement du site, ainsi que des statistiques de visite anonymes. Vous pouvez les accepter ou les refuser. Un refus n'affecte en rien le site.",
            accept: "Accepter", decline: "Refuser", more: "Politique de confidentialité",
            href: "politique-de-confidentialite/", label: "Gestion du consentement" }
    };

    var stored = null;
    try { stored = JSON.parse(localStorage.getItem(CONSENT_KEY)); } catch (e) { stored = null; }
    var expired = !stored || !stored.at ||
      (Date.now() - stored.at) > CONSENT_DAYS * 864e5;

    window.olgaConsent = {
      granted: function () { return !!stored && stored.value === "accept" && !expired; },
      onAccept: function (fn) { if (this.granted()) fn(); }
    };

    if (expired) {
      var lang = document.documentElement.lang;
      var t = T[lang] || T.en;

      // On réutilise le lien de la politique déjà présent dans le pied de page :
      // calculer la profondeur depuis location.pathname casserait sur GitHub
      // Pages, qui sert le site depuis un sous-dossier.
      var ppLink = document.querySelector('.footer__legal a[href*="' + t.href + '"]');
      var ppHref = ppLink ? ppLink.getAttribute("href") : t.href;

      var bar = document.createElement("div");
      bar.className = "cookie-bar";
      bar.setAttribute("role", "dialog");
      bar.setAttribute("aria-label", t.label);
      bar.innerHTML =
        '<p class="cookie-bar__text">' + t.text +
        ' <a href="' + ppHref + '">' + t.more + '</a>.</p>' +
        '<div class="cookie-bar__actions">' +
          '<button class="btn btn--primary" type="button" data-consent="accept">' + t.accept + '</button>' +
          '<button class="btn btn--ghost" type="button" data-consent="decline">' + t.decline + '</button>' +
        '</div>';

      var close = function (value) {
        try { localStorage.setItem(CONSENT_KEY, JSON.stringify({ value: value, at: Date.now() })); } catch (e) {}
        bar.classList.remove("is-open");
        window.setTimeout(function () { bar.remove(); }, 300);
      };
      bar.addEventListener("click", function (ev) {
        var b = ev.target.closest("[data-consent]");
        if (b) close(b.getAttribute("data-consent"));
      });

      document.body.appendChild(bar);
      window.requestAnimationFrame(function () { bar.classList.add("is-open"); });
    }

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
