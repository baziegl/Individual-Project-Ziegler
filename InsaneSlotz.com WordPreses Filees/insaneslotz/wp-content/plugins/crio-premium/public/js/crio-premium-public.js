!function(e){var t={};function n(r){if(t[r])return t[r].exports;var a=t[r]={i:r,l:!1,exports:{}};return e[r].call(a.exports,a,a.exports,n),a.l=!0,a.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var a in e)n.d(r,a,function(t){return e[t]}.bind(null,a));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=4)}({4:function(e,t){var n=n||{};!function(e){"use strict";(n={init:function(){n.addTemplateClasses(),e(window).on("load",(function(){n.setInitialOverlap(),e(".bgtfw-header.template-header #masthead:not( .template-header ) .hamburger").on("click",(function(){n.changeOverlap()}))})),e(window).on("resize",(function(){n.setInitialOverlap()}))},setInitialOverlap:function(){var t=e(".bgtfw-header.template-header #masthead.merged-header"),n=t.outerHeight(!1);768<e(window).width()||0!==n&&(t.css("margin-bottom","-"+n+"px"),t.css("position","relative"))},changeOverlap:function(){var t=e(".bgtfw-header.template-header #masthead.merged-header"),n=t.outerHeight(!1);e(".template-header input[id$=-menu-state]").prop("checked")?(n-=t.find(".sm").outerHeight(!0),e(".template-header > .boldgrid-section").animate({"padding-top":0},"slow"),e(".mce-content-body.content.post-type-crio_page_header > .boldgrid-section").animate({"padding-top":0},"slow"),t.animate({"margin-bottom":"-"+n+"px"},"slow")):(n+=t.find(".sm").outerHeight(!0),e(".template-header > .boldgrid-section").first().css("padding-top",n+"px"),t.css("margin-bottom","-"+n+"px")),t.css("position","relative")},addTemplateClasses:function(){e(document).ready((function(){CrioPremium.hasHeaderTemplate&&e(".bgtfw-header").addClass("template-header")}))}}).init()}(jQuery)}});