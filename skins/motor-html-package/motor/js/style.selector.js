(jQuery.cookie.defaults.path = "/"),
  (function (e) {
    var o = "images/",
      a = o,
      i = {
        filename: "color1.css",
        primary_color: "#ffb535",
        secondary_color: "#f0e797",
        isChanging: !1,
        cookieColor: "noo-selector-color",
        cookieColorSecondary: "noo-selector-color-secondary",
        cookieImagePath: "noo-image-path",
        cookieSkin: "noo-selector-skin",
        cookieLayout: "noo-selector-layout",
        cookiePattern: "noo-selector-pattern",
        cookieOpened: "noo-selector-opened",
        initialize: function () {
          (iThis = this),
            iThis.build(),
            iThis.events(),
            null != e.cookie(iThis.cookieColor) &&
              iThis.setColor(e.cookie(iThis.cookieColor)),
            null != e.cookie(iThis.cookieImagePath) &&
              iThis.setImagePath(e.cookie(iThis.cookieImagePath)),
            null != e.cookie(iThis.cookieSkin) &&
              iThis.setSkin(e.cookie(iThis.cookieSkin)),
            null != e.cookie(iThis.cookieLayout) &&
              iThis.setLayout(e.cookie(iThis.cookieLayout)),
            null != e.cookie(iThis.CookiePattern) &&
              iThis.setPattern(e.cookie(iThis.CookiePattern)),
            null == e.cookie(iThis.cookieOpened) &&
              e.cookie(iThis.cookieOpened, !0);
        },
        build: function () {
          var a = this;
          (style_selector_div = e("<div />")
            .attr("id", "styleSelector")
            .addClass("style-selector visible-md visible-lg")
            ),
            e("body").append(style_selector_div),
            (a.container = e("#styleSelector")),
            a.container.find("div.options-links.mode a").click(function (o) {
              o.preventDefault();
              var a = e(this).parents(".mode");
              a.find("a.active").removeClass("active"),
                e(this).addClass("active"),
                "advanced" == e(this).attr("data-mode")
                  ? e("#styleSelector")
                      .addClass("advanced")
                      .removeClass("basic")
                  : e("#styleSelector")
                      .addClass("basic")
                      .removeClass("advanced");
            });
          var i = [
            {
              Hex1: a.primary_color,
              colorName1: "Orange",
              Hex2: a.secondary_color,
              colorName2: "Blue",
              fileName: "color1.css",
              imagePath: "images/color/color1/",
            },
            {
              Hex1: "#35adff",
              colorName1: "Deep Sky Blue",
              Hex2: "#A17D01",
              colorName2: "Bright Yellow",
              fileName: "color2.css",
              imagePath: "images/color/color2/",
            },
            {
              Hex1: "#35ffe0",
              colorName1: "Medium Aqua Marine",
              Hex2: "#cccccc",
              colorName2: "Light Gray",
              fileName: "color3.css",
              imagePath: "images/color/color3/",
            },
            {
              Hex1: "#ff35a3",
              colorName1: "Deep Pink",
              Hex2: "#d5e5f2",
              colorName2: "Light Grayish Blue",
              fileName: "color4.css",
              imagePath: "images/color/color4/",
            },
            {
              Hex1: "#8fa4a2",
              colorName1: "Dark Grey",
              Hex2: "#e8e555",
              colorName2: "Soft Yellow",
              fileName: "color5.css",
              imagePath: "images/color/color5/",
            },
          ];
          (presetColorsEl = a.container.find("ul[data-type=colors]")),
            e.each(i, function (o) {
              var a = e("<li />").append(
                e("<a />")
                  .css("background-color", i[o].Hex2)
                  .attr({
                    "data-color-hex1": i[o].Hex1,
                    "data-color-name1": i[o].colorName1,
                    "data-color-hex2": i[o].Hex2,
                    "data-color-name2": i[o].colorName2,
                    "data-filename": i[o].fileName,
                    "data-image-path": i[o].imagePath,
                    href: "#",
                    title: i[o].colorName1,
                  })
                  .append(
                    e("<div />")
                      .addClass("triangle-topleft")
                      .css("border-top-color", i[o].Hex1)
                  )
              );
              presetColorsEl.append(a);
            }),
            presetColorsEl.find("a").click(function (o) {
              o.preventDefault(), a.setColor(e(this).attr("data-filename"));
            }),
            presetColorsEl.find("a").click(function (o) {
              o.preventDefault(),
                a.setImagePath(e(this).attr("data-image-path"));
            }),
            a.container.find("div.options-links.layout a").click(function (o) {
              o.preventDefault(), a.setLayout(e(this).attr("data-layout"));
            }),
            a.container.find("div.options-links.skin a").click(function (o) {
              o.preventDefault(), a.setSkin(e(this).attr("data-skin"));
            });
          var t = [
              "bright_squares",
              "random_grey_variations",
              "wild_oliva",
              "denim",
              "subtle_grunge",
              "az_subtle",
              "straws",
              "textured_stripes",
            ],
            n = a.container.find("ul[data-type=patterns]");
          e.each(t, function (a, i) {
            var t = e("<li />").append(
              e("<a />")
                .addClass("pattern")
                .css("background-image", "url(" + o + "patterns/" + i + ".png)")
                .attr({
                  "data-pattern": i,
                  href: "#",
                  title: i.charAt(0).toUpperCase() + i.slice(1),
                })
            );
            n.append(t);
          }),
            n.find("a").click(function (o) {
              o.preventDefault(), a.setPattern(e(this).attr("data-pattern"));
            }),
            a.container.find("a.reset").click(function (e) {
              e.preventDefault(), a.reset();
            });
        },
        events: function () {
          var e = this;
          e.container.find(".selector-title a").click(function (o) {
            o.preventDefault(),
              e.container.hasClass("active")
                ? e.container
                    .animate({ left: "-" + e.container.width() + "px" }, 300)
                    .removeClass("active")
                : e.container.animate({ left: "0" }, 300).addClass("active");
          });
        },
        setColor: function (o) {
          var a = this;
          return a.isChanging
            ? !1
            : (($mainCSS = e("#style-main-color")),
              (cssHref = $mainCSS.attr("href")),
              (cssHref = cssHref.replace(a.filename, o)),
              (a.filename = o),
              $mainCSS.attr("href", cssHref),
              e.cookie(a.cookieColor, o),
              void e(document).trigger("noo-color-changed"));
        },
        setImagePath: function (o) {
          var i = this;
          return i.isChanging
            ? !1
            : (e(".image-live-view").each(function () {
                (src = e(this).attr("src")),
                  (src = src.replace(a, o)),
                  e(this).attr("src", src);
              }),
              (a = o),
              e.cookie(i.cookieImagePath, o),
              void e(document).trigger("noo-color-changed"));
        },
        setSkin: function (o) {
          var a = this;
          "dark" != o && (o = "light");
          var i = a.container.find("div.options-links.skin");
          i.find("a.active").removeClass("active"),
            i.find("a[data-skin=" + o + "]").addClass("active");
          var t = "";
          "dark" == o
            ? (e("body").addClass("dark-style"), (t = o))
            : ((t = ""), e("body").removeClass("dark-style")),
            e.cookie(a.cookieSkin, t);
        },
        updateLogo: function () {
          var a = iThis.container
            .find("div.options-links.skin a.active")
            .attr("data-skin");
          (image_url = "dark" === a ? o + "logo-dark.png" : o + "logo.png"),
            (image_floating_url = o + "logo-dark.png"),
            "" !== image_url &&
              (e(".navbar-brand .noo-logo-img").remove(),
              e(".navbar-brand .noo-logo-retina-img").remove(),
              e(".navbar-brand").append(
                '<img class="noo-logo-img noo-logo-normal" src="' +
                  image_url +
                  '">'
              ),
              e(".navbar-brand").append(
                '<img class="noo-logo-retina-img noo-logo-normal" src="' +
                  image_url +
                  '">'
              ),
              e(".navbar-brand").append(
                '<img class="noo-logo-img noo-logo-floating" src="' +
                  image_floating_url +
                  '">'
              ),
              e(".navbar-brand").append(
                '<img class="noo-logo-retina-img noo-logo-floating" src="' +
                  image_floating_url +
                  '">'
              )),
            e(document).trigger("noo-logo-changed");
        },
        
        setPattern: function (a) {
          var i = this;
          e("body").hasClass("boxed-layout") &&
            (e("body")
              .css("background-image", "url(" + o + "patterns/" + a + ".png)")
              .css("background-repeat", "repeat"),
            e.cookie(i.CookiePattern, a)),
            e(document).trigger("noo-pattern-changed");
        },
        updateCSS: function () {
          iThis = this;
          iThis.container.find("div.options-links.skin a.active"),
            iThis.container
              .find("div.options-links.skin a.active")
              .attr("data-skin");
        },
        reset: function () {
          var o = this;
          e.removeCookie(o.cookieColor),
            e.removeCookie(o.cookieImagePath),
            e.removeCookie(o.cookieSkin),
            location.reload();
        },
      };
    e(document).ready(function () {
      i.initialize();
    });
  })(jQuery);
