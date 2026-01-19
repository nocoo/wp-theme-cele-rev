// ../../../../../private/tmp/production_with_fitvids_1768813192214.js
/*!
 * FitVids 1.1
 *
 * Copyright 2013, Chris Coyier - http://css-tricks.com + Dave Rupert - http://daverupert.com
 * Credit to Thierry Koblentz - http://www.alistapart.com/articles/creating-intrinsic-ratios-for-video/
 * Released under the WTFPL license - http://sam.zoy.org/wtfpl/
 *
 */
(function($) {
  $.fn.fitVids = function(options) {
    var settings = {
      customSelector: null,
      ignore: null
    };
    if (!document.getElementById("fit-vids-style")) {
      var head = document.head || document.getElementsByTagName("head")[0];
      var css = ".fluid-width-video-wrapper{width:100%;position:relative;padding:0;}.fluid-width-video-wrapper iframe,.fluid-width-video-wrapper object,.fluid-width-video-wrapper embed {position:absolute;top:0;left:0;width:100%;height:100%;}";
      var div = document.createElement("div");
      div.innerHTML = '<p>x</p><style id="fit-vids-style">' + css + "</style>";
      head.appendChild(div.childNodes[1]);
    }
    if (options) {
      $.extend(settings, options);
    }
    return this.each(function() {
      var selectors = [
        'iframe[src*="player.vimeo.com"]',
        'iframe[src*="youtube.com"]',
        'iframe[src*="youtube-nocookie.com"]',
        'iframe[src*="kickstarter.com"][src*="video.html"]',
        "object",
        "embed"
      ];
      if (settings.customSelector) {
        selectors.push(settings.customSelector);
      }
      var ignoreList = ".fitvidsignore";
      if (settings.ignore) {
        ignoreList = ignoreList + ", " + settings.ignore;
      }
      var $allVideos = $(this).find(selectors.join(","));
      $allVideos = $allVideos.not("object object");
      $allVideos = $allVideos.not(ignoreList);
      $allVideos.each(function() {
        var $this = $(this);
        if ($this.parents(ignoreList).length > 0) {
          return;
        }
        if (this.tagName.toLowerCase() === "embed" && $this.parent("object").length || $this.parent(".fluid-width-video-wrapper").length) {
          return;
        }
        if (!$this.css("height") && !$this.css("width") && (isNaN($this.attr("height")) || isNaN($this.attr("width")))) {
          $this.attr("height", 9);
          $this.attr("width", 16);
        }
        var height = this.tagName.toLowerCase() === "object" || $this.attr("height") && !isNaN(parseInt($this.attr("height"), 10)) ? parseInt($this.attr("height"), 10) : $this.height(), width = !isNaN(parseInt($this.attr("width"), 10)) ? parseInt($this.attr("width"), 10) : $this.width(), aspectRatio = height / width;
        if (!$this.attr("name")) {
          var videoName = "fitvid" + $.fn.fitVids._count;
          $this.attr("name", videoName);
          $.fn.fitVids._count++;
        }
        $this.wrap('<div class="fluid-width-video-wrapper"></div>').parent(".fluid-width-video-wrapper").css("padding-top", aspectRatio * 100 + "%");
        $this.removeAttr("height").removeAttr("width");
      });
    });
  };
  $.fn.fitVids._count = 0;
})(window.jQuery || window.Zepto);
jQuery(document).ready(function($) {
  var body = $("body");
  var main = $("#main");
  var overflowContainer = $("#overflow-container");
  var maxWidth = $("#max-width");
  var headerImage = $("#header-image");
  var toggleNavigation = $("#toggle-navigation");
  var menuPrimaryContainer = $("#menu-primary-container");
  var menuPrimaryItems = $("#menu-primary-items");
  var toggleDropdown = $(".toggle-dropdown");
  var sidebar = $("#main-sidebar");
  var sidebarPrimaryContainer = $("#sidebar-primary-container");
  var sidebarInner = $("#sidebar-inner");
  var adminBar = 0;
  if (body.hasClass("admin-bar")) {
    adminBar = 32;
  }
  var adjustment = 24;
  var lastScrollTop = 0;
  var scrollTracking = false;
  assignMenuItemDelays();
  setMainMinHeight();
  setupSidebar();
  objectFitAdjustment();
  sidebarAdjustment();
  menuKeyboardAccess();
  toggleNavigation.on("click", openPrimaryMenu);
  toggleDropdown.on("click", openDropdownMenu);
  $(window).on("resize", function() {
    objectFitAdjustment();
    setupSidebar();
    sidebarAdjustment();
    setMainMinHeight();
  });
  $(document.body).on("post-load", function() {
    objectFitAdjustment();
  });
  $(".post-content").fitVids({
    customSelector: 'iframe[src*="dailymotion.com"], iframe[src*="slideshare.net"], iframe[src*="animoto.com"], iframe[src*="blip.tv"], iframe[src*="funnyordie.com"], iframe[src*="hulu.com"], iframe[src*="ted.com"], iframe[src*="wordpress.tv"]'
  });
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
      scrollTracking = false;
    }
  }
  function openPrimaryMenu() {
    if (menuPrimaryContainer.hasClass("open")) {
      menuPrimaryContainer.removeClass("open");
      $(this).removeClass("open");
      sidebarPrimaryContainer.removeClass("open");
      $(this).children("span").text(ct_cele_objectL10n.openMenu);
      $(this).attr("aria-expanded", "false");
    } else {
      menuPrimaryContainer.addClass("open");
      $(this).addClass("open");
      sidebarPrimaryContainer.addClass("open");
      $(this).children("span").text(ct_cele_objectL10n.closeMenu);
      $(this).attr("aria-expanded", "true");
    }
  }
  function openDropdownMenu() {
    var menuItem = $(this).parent();
    var subMenu = $(this).siblings("ul");
    var parentList = menuItem.parent();
    if (menuItem.hasClass("open")) {
      menuItem.removeClass("open");
      $(this).children("span").text(ct_cele_objectL10n.openMenu);
      $(this).attr("aria-expanded", "false");
      subMenu.css("max-height", "0");
      subMenu.one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", function(e) {
        setMainMinHeight();
        setupSidebar();
      });
      menuKeyboardAccess(menuItem, "close");
    } else {
      menuItem.addClass("open");
      $(this).children("span").text(ct_cele_objectL10n.closeMenu);
      $(this).attr("aria-expanded", "true");
      var subMenuHeight = 0;
      subMenu.children("li").each(function() {
        subMenuHeight = subMenuHeight + $(this).height();
      });
      subMenu.css("max-height", subMenuHeight);
      if (parentList.hasClass("sub-menu")) {
        parentList.css("max-height", parseInt(parentList.css("max-height")) + subMenuHeight + "px");
      }
      parentList.one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", function(e) {
        setMainMinHeight();
        setupSidebar();
      });
      menuKeyboardAccess(menuItem, "open");
    }
  }
  function assignMenuItemDelays() {
    var counter = 0;
    menuPrimaryItems.find("ul").each(function() {
      $(this).children("li").each(function() {
        $(this).css("transition-delay", "0." + counter + "s");
        counter++;
      });
      counter = 0;
    });
    var currentMenuItem = $(".current-menu-ancestor");
    currentMenuItem.addClass("open");
    currentMenuItem.children("ul").one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", function(e) {
      setMainMinHeight();
      setupSidebar();
    });
  }
  function positionSidebar() {
    if (window.innerWidth < 900) {
      return;
    }
    var windowBottom = $(window).scrollTop() + window.innerHeight;
    var sidebarBottom = sidebarInner.offset().top + sidebarInner.outerHeight(true);
    var scrolledUp = false;
    var st = $(this).scrollTop();
    var rtl = false;
    if (body.hasClass("rtl")) {
      rtl = true;
    }
    function sidePositioning(rtl2, offset) {
      if (rtl2 && offset) {
        sidebar.css("right", maxWidth.offset().left);
      } else if (rtl2) {
        sidebar.css("right", "");
      } else if (offset) {
        sidebar.css("left", maxWidth.offset().left);
      } else {
        sidebar.css("left", "");
      }
    }
    if (st < lastScrollTop) {
      scrolledUp = true;
    }
    lastScrollTop = st;
    if (scrolledUp == true && sidebar.hasClass("fixed-bottom")) {
      sidebar.css("top", sidebar.offset().top - adjustment + "px");
      sidePositioning(rtl, false);
      sidebar.addClass("down-page");
      sidebar.removeClass("fixed-bottom");
    } else if (scrolledUp == true && sidebar.hasClass("down-page") && sidebar.offset().top - adminBar >= $(window).scrollTop()) {
      sidebar.removeClass("down-page");
      sidebar.addClass("fixed-top");
      sidebar.css("top", "");
      sidePositioning(rtl, true);
    } else if (sidebar.hasClass("fixed-top") && $(window).scrollTop() <= parseInt(overflowContainer.offset().top)) {
      sidebar.removeClass("fixed-top");
      sidePositioning(rtl, false);
    } else if ((sidebar.hasClass("fixed-top") || sidebar.hasClass("fixed")) && scrolledUp == false) {
      sidebar.css("top", sidebar.offset().top - adjustment + "px");
      sidePositioning(rtl, false);
      sidebar.removeClass("fixed-top");
      sidebar.removeClass("fixed");
      sidebar.addClass("down-page");
    } else if (windowBottom >= sidebarBottom && scrolledUp == false && $(window).scrollTop() >= adjustment) {
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
    if ($("#wpadminbar").length > 0) {
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
    var sidebarHeight = sidebarInner.outerHeight(true) + sidebar.offset().top;
    if (sidebarHeight < window.innerHeight) {
      main.css("min-height", window.innerHeight);
    } else {
      main.css("min-height", sidebarHeight);
    }
  }
  function objectFitAdjustment() {
    if (!("object-fit" in document.body.style)) {
      $(".featured-image").each(function() {
        if (!$(this).parent().parent(".entry").hasClass("ratio-natural")) {
          var image = $(this).children("img").add($(this).children("a").children("img"));
          if (image.hasClass("no-object-fit")) {
            return;
          }
          image.addClass("no-object-fit");
          if (image.outerWidth() < $(this).outerWidth()) {
            image.css({
              width: "100%",
              "min-width": "100%",
              "max-width": "100%",
              height: "auto",
              "min-height": "100%",
              "max-height": "none"
            });
          }
          if (image.outerHeight() < $(this).outerHeight()) {
            image.css({
              height: "100%",
              "min-height": "100%",
              "max-height": "100%",
              width: "auto",
              "min-width": "100%",
              "max-width": "none"
            });
          }
        }
      });
    }
  }
  function menuKeyboardAccess(listItem, status) {
    var tabindex = 0;
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
  if ($("#scroll-to-top").length !== 0) {
    $(window).on("scroll", function() {
      if ($(this).scrollTop() >= 200) {
        $("#scroll-to-top").addClass("visible");
      } else {
        $("#scroll-to-top").removeClass("visible");
      }
    });
    $("#scroll-to-top").click(function(e) {
      $("body,html").animate({
        scrollTop: 0
      }, 600);
      $(this).blur();
    });
  }
});
window.addEventListener("hashchange", function(event) {
  var element = document.getElementById(location.hash.substring(1));
  if (element) {
    if (!/^(?:a|select|input|button|textarea)$/i.test(element.tagName)) {
      element.tabIndex = -1;
    }
    element.focus();
  }
}, false);
