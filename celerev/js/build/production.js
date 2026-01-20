// celerev/js/production.js
jQuery(document).ready(function($) {
  const body = $("body");
  const main = $("#main");
  const overflowContainer = $("#overflow-container");
  const maxWidth = $("#max-width");
  const headerImage = $("#header-image");
  const toggleNavigation = $("#toggle-navigation");
  const menuPrimaryContainer = $("#menu-primary-container");
  const menuPrimaryItems = $("#menu-primary-items");
  const toggleDropdown = $(".toggle-dropdown");
  const sidebar = $("#main-sidebar");
  const sidebarPrimaryContainer = $("#sidebar-primary-container");
  const sidebarInner = $("#sidebar-inner");
  const scrollToTopButton = $("#scroll-to-top");
  const wpadminbar = $("#wpadminbar");
  let adminBar = 0;
  const hasAdminBar = body.hasClass("admin-bar");
  if (hasAdminBar) {
    adminBar = 32;
  }
  let adjustment = 24;
  let lastScrollTop = 0;
  let scrollTracking = false;
  let resizeTimeout;
  let scrollTicking = false;
  assignMenuItemDelays();
  setMainMinHeight();
  setupSidebar();
  sidebarAdjustment();
  menuKeyboardAccess();
  toggleNavigation.on("click", openPrimaryMenu);
  toggleDropdown.on("click", openDropdownMenu);
  $(window).on("resize", () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
      setupSidebar();
      sidebarAdjustment();
      setMainMinHeight();
    }, 150);
  });
  $(document.body).on("post-load", () => {});
  function setupSidebar() {
    if (window.innerWidth > 899) {
      if (sidebarInner.outerHeight(true) < window.innerHeight && headerImage.length == 0) {
        sidebar.addClass("fixed");
        sidebarAdjustment();
      } else {
        if (scrollTracking == false) {
          $(window).on("scroll resize", positionSidebar);
          scrollTracking = true;
        }
      }
    } else {
      if (scrollTracking) {
        $(window).off("scroll resize", positionSidebar);
        scrollTracking = false;
      }
    }
  }
  function toggleMenuState(container, button, l10nKeys, additionalElements = []) {
    const isOpen = container.hasClass("open");
    if (isOpen) {
      container.removeClass("open");
      if (button) {
        button.removeClass("open");
        button.find("span").text(ct_cele_objectL10n[l10nKeys.open]);
        button.attr("aria-expanded", "false");
      }
      additionalElements.forEach((el) => el.removeClass("open"));
    } else {
      container.addClass("open");
      if (button) {
        button.addClass("open");
        button.find("span").text(ct_cele_objectL10n[l10nKeys.close]);
        button.attr("aria-expanded", "true");
      }
      additionalElements.forEach((el) => el.addClass("open"));
    }
  }
  function openPrimaryMenu() {
    toggleMenuState(menuPrimaryContainer, $(this), { open: "openMenu", close: "closeMenu" }, [sidebarPrimaryContainer]);
  }
  function openDropdownMenu() {
    const menuItem = $(this).parent();
    const subMenu = $(this).siblings("ul");
    const parentList = menuItem.parent();
    const isOpen = menuItem.hasClass("open");
    if (isOpen) {
      menuItem.removeClass("open");
      $(this).find("span").text(ct_cele_objectL10n.openMenu);
      $(this).attr("aria-expanded", "false");
      subMenu.css("max-height", "0");
      subMenu.one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", () => {
        setMainMinHeight();
        setupSidebar();
      });
      menuKeyboardAccess(menuItem, "close");
    } else {
      menuItem.addClass("open");
      $(this).find("span").text(ct_cele_objectL10n.closeMenu);
      $(this).attr("aria-expanded", "true");
      const subMenuChildren = subMenu.children("li");
      let subMenuHeight = 0;
      subMenuChildren.each(function() {
        subMenuHeight += $(this).outerHeight();
      });
      subMenu.css("max-height", subMenuHeight);
      if (parentList.hasClass("sub-menu")) {
        parentList.css("max-height", parseInt(parentList.css("max-height")) + subMenuHeight + "px");
      }
      parentList.one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", () => {
        setMainMinHeight();
        setupSidebar();
      });
      menuKeyboardAccess(menuItem, "open");
    }
  }
  function assignMenuItemDelays() {
    let counter = 0;
    menuPrimaryItems.find("ul").each(function() {
      $(this).children("li").each(function() {
        $(this).css("transition-delay", `${counter / 10}s`);
        counter++;
      });
      counter = 0;
    });
    const currentMenuItem = $(".current-menu-ancestor");
    currentMenuItem.addClass("open");
    currentMenuItem.children("ul").one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", () => {
      setMainMinHeight();
      setupSidebar();
    });
  }
  function positionSidebar() {
    if (window.innerWidth < 900) {
      return;
    }
    const windowBottom = $(window).scrollTop() + window.innerHeight;
    const sidebarBottom = sidebarInner.offset().top + sidebarInner.outerHeight(true);
    let scrolledUp = false;
    const st = $(this).scrollTop();
    let rtl = false;
    if (body.hasClass("rtl")) {
      rtl = true;
    }
    const sidePositioning = (rtl2, offset) => {
      if (rtl2 && offset) {
        sidebar.css("right", maxWidth.offset().left);
      } else if (rtl2) {
        sidebar.css("right", "");
      } else if (offset) {
        sidebar.css("left", maxWidth.offset().left);
      } else {
        sidebar.css("left", "");
      }
    };
    if (st < lastScrollTop) {
      scrolledUp = true;
    }
    lastScrollTop = st;
    if (scrolledUp && sidebar.hasClass("fixed-bottom")) {
      sidebar.css("top", sidebar.offset().top - adjustment + "px");
      sidePositioning(rtl, false);
      sidebar.addClass("down-page");
      sidebar.removeClass("fixed-bottom");
    } else if (scrolledUp && sidebar.hasClass("down-page") && sidebar.offset().top - adminBar >= $(window).scrollTop()) {
      sidebar.removeClass("down-page");
      sidebar.addClass("fixed-top");
      sidebar.css("top", "");
      sidePositioning(rtl, true);
    } else if (sidebar.hasClass("fixed-top") && $(window).scrollTop() <= parseInt(overflowContainer.offset().top)) {
      sidebar.removeClass("fixed-top");
      sidePositioning(rtl, false);
    } else if ((sidebar.hasClass("fixed-top") || sidebar.hasClass("fixed")) && !scrolledUp) {
      sidebar.css("top", sidebar.offset().top - adjustment + "px");
      sidePositioning(rtl, false);
      sidebar.removeClass("fixed-top");
      sidebar.removeClass("fixed");
      sidebar.addClass("down-page");
    } else if (windowBottom >= sidebarBottom && !scrolledUp && $(window).scrollTop() >= adjustment) {
      sidebar.addClass("fixed-bottom");
      if (sidebarInner.outerHeight(true) >= window.innerHeight) {
        sidebar.css("top", "");
      } else {
        sidebar.css("top", -24);
      }
      sidePositioning(rtl, true);
      sidebar.removeClass("down-page");
    }
  }
  function sidebarAdjustment() {
    if (window.innerWidth >= 1100) {
      adjustment = 24;
    } else if (window.innerWidth >= 1000) {
      adjustment = 12;
    } else if (window.innerWidth >= 890) {
      adjustment = 0;
    }
    if (hasAdminBar || wpadminbar.length > 0) {
      adjustment += 32;
      if (sidebar.hasClass("fixed")) {
        sidebar.css("top", "32px");
      }
    }
    if (headerImage.length > 0) {
      adjustment += headerImage.outerHeight(true);
    }
    if (sidebar.hasClass("fixed")) {
      sidebar.css("left", maxWidth.offset().left);
    }
  }
  function setMainMinHeight() {
    const sidebarHeight = sidebarInner.outerHeight(true) + sidebar.offset().top;
    if (sidebarHeight < window.innerHeight) {
      main.css("min-height", window.innerHeight);
    } else {
      main.css("min-height", sidebarHeight);
    }
  }
  function menuKeyboardAccess(listItem, status) {
    let tabindex = 0;
    if (status == "close") {
      tabindex = -1;
    }
    if (listItem) {
      listItem.children("ul").children("li").children("a, button").attr("tabindex", tabindex);
    } else {
      menuPrimaryItems.find("ul").each(function() {
        $(this).children("li").children().attr("tabindex", -1);
      });
    }
  }
  if (scrollToTopButton.length !== 0) {
    $(window).on("scroll", () => {
      if (!scrollTicking) {
        window.requestAnimationFrame(() => {
          if ($(window).scrollTop() >= 200) {
            scrollToTopButton.addClass("visible");
          } else {
            scrollToTopButton.removeClass("visible");
          }
          scrollTicking = false;
        });
        scrollTicking = true;
      }
    }, { passive: true });
    scrollToTopButton.click(() => {
      $("body,html").animate({
        scrollTop: 0
      }, 600);
      $(this).blur();
    });
  }
});
window.addEventListener("hashchange", () => {
  const element = document.getElementById(location.hash.substring(1));
  if (element) {
    if (!/^(?:a|select|input|button|textarea)$/i.test(element.tagName)) {
      element.tabIndex = -1;
    }
    element.focus();
  }
}, false);
