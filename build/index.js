!function(){"use strict";var e,o={273:function(){var e=window.wp.blocks,o=window.wp.element,r=window.wp.i18n,n=window.wp.blockEditor;(0,e.registerBlockType)("create-block/like-post-block",{edit:function(){return(0,o.createElement)("p",(0,n.useBlockProps)(),(0,r.__)("Like Post Block – hello from the editor!","like-post-block"))},save:function(){return(0,o.createElement)("p",n.useBlockProps.save(),(0,r.__)("Like Post Block – hello from the saved content!","like-post-block"))}})}},r={};function n(e){var t=r[e];if(void 0!==t)return t.exports;var i=r[e]={exports:{}};return o[e](i,i.exports,n),i.exports}n.m=o,e=[],n.O=function(o,r,t,i){if(!r){var c=1/0;for(f=0;f<e.length;f++){r=e[f][0],t=e[f][1],i=e[f][2];for(var l=!0,u=0;u<r.length;u++)(!1&i||c>=i)&&Object.keys(n.O).every((function(e){return n.O[e](r[u])}))?r.splice(u--,1):(l=!1,i<c&&(c=i));if(l){e.splice(f--,1);var s=t();void 0!==s&&(o=s)}}return o}i=i||0;for(var f=e.length;f>0&&e[f-1][2]>i;f--)e[f]=e[f-1];e[f]=[r,t,i]},n.o=function(e,o){return Object.prototype.hasOwnProperty.call(e,o)},function(){var e={826:0,431:0};n.O.j=function(o){return 0===e[o]};var o=function(o,r){var t,i,c=r[0],l=r[1],u=r[2],s=0;if(c.some((function(o){return 0!==e[o]}))){for(t in l)n.o(l,t)&&(n.m[t]=l[t]);if(u)var f=u(n)}for(o&&o(r);s<c.length;s++)i=c[s],n.o(e,i)&&e[i]&&e[i][0](),e[i]=0;return n.O(f)},r=self.webpackChunklike_post_block=self.webpackChunklike_post_block||[];r.forEach(o.bind(null,0)),r.push=o.bind(null,r.push.bind(r))}();var t=n.O(void 0,[431],(function(){return n(273)}));t=n.O(t)}();