(function () {
  "use strict";

  var CART_KEY = "sabor_cart_v1";

  /* ---------------- Helpers ---------------- */
  function formatMoney(n) {
    return "$" + n.toFixed(2);
  }

  function getCart() {
    try {
      var raw = window.localStorage.getItem(CART_KEY);
      return raw ? JSON.parse(raw) : [];
    } catch (e) {
      return [];
    }
  }

  function saveCart(cart) {
    try {
      window.localStorage.setItem(CART_KEY, JSON.stringify(cart));
    } catch (e) {
      /* localStorage unavailable (private mode etc.) — fail silently */
    }
  }

  function cartCount(cart) {
    return cart.reduce(function (sum, item) {
      return sum + item.qty;
    }, 0);
  }

  function cartTotal(cart) {
    return cart.reduce(function (sum, item) {
      return sum + item.qty * item.price;
    }, 0);
  }

  function playSound(id) {
    var el = document.querySelector("#" + id);
    if (!el) return;
    el.currentTime = 0;
    el.play().catch(function () {
      /* Autoplay can be blocked by the browser in rare cases — fail silently */
    });
  }

  /* ---------------- Footer year ---------------- */
  document.querySelectorAll("[data-year]").forEach(function (el) {
    el.textContent = new Date().getFullYear();
  });

  /* ---------------- Mobile nav toggle ---------------- */
  var navToggle = document.querySelector(".nav-toggle");
  var mainNav = document.querySelector(".main-nav");
  if (navToggle && mainNav) {
    navToggle.addEventListener("click", function () {
      var isOpen = mainNav.classList.toggle("is-open");
      navToggle.setAttribute("aria-expanded", String(isOpen));
    });
    mainNav.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        mainNav.classList.remove("is-open");
        navToggle.setAttribute("aria-expanded", "false");
      });
    });
  }

  /* ---------------- Cart badge (present on every page) ---------------- */
  function refreshCartBadge() {
    var badge = document.querySelector("[data-cart-count]");
    if (!badge) return;
    var count = cartCount(getCart());
    badge.textContent = count;
    badge.parentElement.setAttribute(
      "aria-label",
      count === 1 ? "View order, 1 item" : "View order, " + count + " items",
    );
  }
  refreshCartBadge();

  /* ---------------- Menu category tabs ---------------- */
  var tabs = document.querySelectorAll(".menu-tab");
  if (tabs.length) {
    tabs.forEach(function (tab) {
      tab.addEventListener("click", function () {
        tabs.forEach(function (t) {
          t.setAttribute("aria-selected", "false");
        });
        tab.setAttribute("aria-selected", "true");

        var targetId = tab.getAttribute("aria-controls");
        document.querySelectorAll(".menu-category").forEach(function (panel) {
          panel.classList.toggle("is-active", panel.id === targetId);
        });
      });
    });
  }

  /* ---------------- Quantity steppers on menu tickets ---------------- */
  document.querySelectorAll(".ticket[data-id]").forEach(function (ticket) {
    var qtyValue = ticket.querySelector(".qty-value");
    var minus = ticket.querySelector(".qty-minus");
    var plus = ticket.querySelector(".qty-plus");
    var addBtn = ticket.querySelector(".add-to-cart");

    function getQty() {
      return parseInt(qtyValue.textContent, 10) || 1;
    }
    function setQty(n) {
      qtyValue.textContent = String(Math.max(1, Math.min(9, n)));
    }

    if (minus)
      minus.addEventListener("click", function () {
        setQty(getQty() - 1);
      });
    if (plus)
      plus.addEventListener("click", function () {
        setQty(getQty() + 1);
      });

    if (addBtn) {
      addBtn.addEventListener("click", function () {
        var id = ticket.getAttribute("data-id");
        var name = ticket.getAttribute("data-name");
        var price = parseFloat(ticket.getAttribute("data-price"));
        var qty = getQty();

        var cart = getCart();
        var existing = cart.find(function (item) {
          return item.id === id;
        });
        if (existing) {
          existing.qty += qty;
        } else {
          cart.push({ id: id, name: name, price: price, qty: qty });
        }
        saveCart(cart);
        setQty(1);
        renderCartPanel();
        refreshCartBadge();
        playSound("cart-add-sound");

        addBtn.textContent = "Added \u2713";
        addBtn.classList.add("btn-lime");
        window.setTimeout(function () {
          addBtn.textContent = "Add to order";
          addBtn.classList.remove("btn-lime");
        }, 1100);

        announce(name + " added to your order.");
      });
    }
  });

  /* ---------------- Cart panel (menu.html) ---------------- */
  function renderCartPanel() {
    var list = document.querySelector("[data-cart-list]");
    if (!list) return;
    var cart = getCart();
    list.innerHTML = "";

    cart.forEach(function (item) {
      var li = document.createElement("li");
      li.className = "cart-item";
      li.innerHTML =
        "<div>" +
        '<div class="name">' +
        escapeHtml(item.name) +
        "</div>" +
        '<div class="meta">' +
        item.qty +
        " \u00d7 " +
        formatMoney(item.price) +
        "</div>" +
        '<button type="button" class="remove" data-remove="' +
        item.id +
        '">Remove</button>' +
        "</div>" +
        '<div class="meta">' +
        formatMoney(item.qty * item.price) +
        "</div>";
      list.appendChild(li);
    });

    list.querySelectorAll("[data-remove]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var id = btn.getAttribute("data-remove");
        var cart = getCart().filter(function (item) {
          return item.id !== id;
        });
        saveCart(cart);
        renderCartPanel();
        refreshCartBadge();
      });
    });

    var subtotalEl = document.querySelector("[data-cart-subtotal]");
    var taxEl = document.querySelector("[data-cart-tax]");
    var totalEl = document.querySelector("[data-cart-total]");
    var checkoutBtn = document.querySelector("[data-checkout-link]");

    var subtotal = cartTotal(cart);
    var tax = subtotal * 0.08;
    var total = subtotal + tax;

    if (subtotalEl) subtotalEl.textContent = formatMoney(subtotal);
    if (taxEl) taxEl.textContent = formatMoney(tax);
    if (totalEl) totalEl.textContent = formatMoney(total);
    if (checkoutBtn) checkoutBtn.toggleAttribute("disabled", cart.length === 0);
  }
  renderCartPanel();

  /* ---------------- Checkout page ---------------- */
  var summaryTable = document.querySelector("[data-summary-table]");
  if (summaryTable) {
    var cart = getCart();
    var tbody = summaryTable.querySelector("tbody");
    var emptyNote = document.querySelector("[data-empty-cart]");
    var checkoutForm = document.querySelector("#checkout-form");

    if (cart.length === 0) {
      summaryTable.hidden = true;
      if (checkoutForm) checkoutForm.hidden = true;
      if (emptyNote) emptyNote.hidden = false;
    } else {
      if (emptyNote) emptyNote.hidden = true;
      cart.forEach(function (item) {
        var tr = document.createElement("tr");
        tr.innerHTML =
          "<td>" +
          escapeHtml(item.name) +
          "</td>" +
          "<td>" +
          item.qty +
          "</td>" +
          "<td>" +
          formatMoney(item.price) +
          "</td>" +
          "<td>" +
          formatMoney(item.price * item.qty) +
          "</td>";
        tbody.appendChild(tr);
      });
      var subtotal = cartTotal(cart);
      var tax = subtotal * 0.08;
      var total = subtotal + tax;
      document.querySelector("[data-summary-subtotal]").textContent =
        formatMoney(subtotal);
      document.querySelector("[data-summary-tax]").textContent =
        formatMoney(tax);
      document.querySelector("[data-summary-total]").textContent =
        formatMoney(total);
    }
  }

  /* ---------------- Gallery lightbox ---------------- */
  var lightbox = document.querySelector("[data-lightbox]");
  if (lightbox) {
    var lightboxImg = lightbox.querySelector("img");
    var lightboxCaption = lightbox.querySelector("[data-lightbox-caption]");
    var closeBtn = lightbox.querySelector(".lightbox-close");
    var lastFocused = null;

    function openLightbox(trigger) {
      lastFocused = trigger;
      var fullSrc =
        trigger.getAttribute("data-full") || trigger.querySelector("img").src;
      var caption = trigger.getAttribute("data-caption") || "";
      lightboxImg.src = fullSrc;
      lightboxImg.alt = caption;
      lightboxCaption.textContent = caption;
      lightbox.classList.add("is-open");
      lightbox.setAttribute("aria-hidden", "false");
      closeBtn.focus();
      document.addEventListener("keydown", onKeydown);
    }

    function closeLightbox() {
      lightbox.classList.remove("is-open");
      lightbox.setAttribute("aria-hidden", "true");
      document.removeEventListener("keydown", onKeydown);
      if (lastFocused) lastFocused.focus();
    }

    function onKeydown(e) {
      if (e.key === "Escape") closeLightbox();
    }

    document.querySelectorAll(".gallery-item").forEach(function (item) {
      item.addEventListener("click", function () {
        openLightbox(item);
      });
    });
    closeBtn.addEventListener("click", closeLightbox);
    lightbox.addEventListener("click", function (e) {
      if (e.target === lightbox) closeLightbox();
    });
  }

  /* ---------------- Accessible live announcer ---------------- */
  var liveRegion = document.querySelector("[data-live-region]");
  function announce(msg) {
    if (!liveRegion) return;
    liveRegion.textContent = "";
    window.setTimeout(function () {
      liveRegion.textContent = msg;
    }, 50);
  }

  function escapeHtml(str) {
    var div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
  }

  /* ---------------- Form validation (contact page) ---------------- */
  var contactForm = document.querySelector("#contact-form");
  if (contactForm) {
    var feedback = contactForm.querySelector(".form-feedback");

    var validators = {
      name: function (v) {
        return v.trim().length >= 2 || "Please enter your full name.";
      },
      email: function (v) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(v.trim()) || "Please enter a valid email address.";
      },
      phone: function (v) {
        if (!v.trim()) return true; // optional
        var re = /^[0-9+()\-.\s]{7,}$/;
        return re.test(v.trim()) || "Please enter a valid phone number.";
      },
      partySize: function (v) {
        if (!v) return true;
        var n = Number(v);
        return (n >= 1 && n <= 20) || "Party size should be between 1 and 20.";
      },
      message: function (v) {
        return (
          v.trim().length >= 10 || "Message should be at least 10 characters."
        );
      },
    };

    function validateField(field) {
      var name = field.name;
      var errorEl = document.querySelector('[data-error-for="' + name + '"]');
      if (!validators[name]) return true;
      var result = validators[name](field.value);
      if (result === true) {
        if (errorEl) errorEl.textContent = "";
        field.setAttribute("aria-invalid", "false");
        return true;
      } else {
        if (errorEl) errorEl.textContent = result;
        field.setAttribute("aria-invalid", "true");
        return false;
      }
    }

    Array.prototype.forEach.call(contactForm.elements, function (field) {
      if (!field.name || !validators[field.name]) return;
      field.addEventListener("blur", function () {
        validateField(field);
      });
    });

    contactForm.addEventListener("submit", function (e) {
      e.preventDefault();
      var valid = true;
      Array.prototype.forEach.call(contactForm.elements, function (field) {
        if (field.name && validators[field.name]) {
          if (!validateField(field)) valid = false;
        }
      });

      feedback.classList.remove("success", "error");
      feedback.classList.add("is-visible");

      if (valid) {
        feedback.classList.add("success");
        feedback.textContent =
          "Thanks! Your message has been received. We'll get back to you within 1 business day.";
        contactForm.reset();
        playSound("message-sent-sound");
      } else {
        feedback.classList.add("error");
        feedback.textContent =
          "Please fix the highlighted fields and try again.";
        var firstInvalid = contactForm.querySelector('[aria-invalid="true"]');
        if (firstInvalid) firstInvalid.focus();
      }
      feedback.setAttribute("tabindex", "-1");
      feedback.focus();
    });
  }

  /* ---------------- Checkout form (fake submit) ---------------- */
  var checkoutForm = document.querySelector("#checkout-form");
  if (checkoutForm) {
    var checkoutFeedback = checkoutForm.querySelector(".form-feedback");
    checkoutForm.addEventListener("submit", function (e) {
      e.preventDefault();
      if (!checkoutForm.checkValidity()) {
        checkoutForm.reportValidity();
        return;
      }
      saveCart([]);
      refreshCartBadge();
      playSound("order-sound");

      checkoutFeedback.classList.remove("error");
      checkoutFeedback.classList.add("success", "is-visible");
      checkoutFeedback.textContent =
        "Order placed! A text confirmation would be sent to you here.";
      checkoutForm.reset();
      var table = document.querySelector("[data-summary-table]");
      if (table) table.hidden = true;
      checkoutForm.hidden = true;
      checkoutFeedback.setAttribute("tabindex", "-1");
      checkoutFeedback.focus();
    });
  }
})();
