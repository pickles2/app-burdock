!function(n){var t={};function e(o){if(t[o])return t[o].exports;var i=t[o]={i:o,l:!1,exports:{}};return n[o].call(i.exports,i,i.exports,e),i.l=!0,i.exports}e.m=n,e.c=t,e.d=function(n,t,o){e.o(n,t)||Object.defineProperty(n,t,{configurable:!1,enumerable:!0,get:o})},e.n=function(n){var t=n&&n.__esModule?function(){return n.default}:function(){return n};return e.d(t,"a",t),t},e.o=function(n,t){return Object.prototype.hasOwnProperty.call(n,t)},e.p="/",e(e.s=322)}({322:function(n,t,e){n.exports=e(323)},323:function(n,t){$(window).on("load",function(){var n=window.filename||"/";function t(n,t,e,o){o=o||function(){},$.ajax({type:"post",url:window.contCommonFileEditorGpiEndpoint,headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},contentType:"application/json",dataType:"json",data:JSON.stringify({method:n,filename:t,to:e.to,px_command:e.px_command,bin:e.bin}),success:function(n){o(n)}})}(window.remoteFinder=new RemoteFinder(document.getElementById("cont-finder"),{gpiBridge:function(n,t){$.ajax({type:"post",url:window.contRemoteFinderGpiEndpoint,headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},contentType:"application/json",dataType:"json",data:JSON.stringify({data:JSON.stringify(n)}),success:function(n){t(n)}})},open:function(n,t){px2style.loading();var e=window.contCommonFileEditorEndpoint+"?filename="+encodeURIComponent(n.path);window.open(e),px2style.closeLoading(),t(!0)},mkdir:function(n,t){var e=$("<div>").html($("#template-mkdir").html());e.find(".cont_current_dir").text(n),e.find("[name=dirname]").on("change keyup",function(){e.find("[name=dirname]").val().match(/\.html?$/i)?e.find(".cont_html_ext_option").show():e.find(".cont_html_ext_option").hide()}),px2style.modal({title:"Create new Directory",body:e,buttons:[$('<button type="button" class="px2-btn">').text("Cancel").on("click",function(n){px2style.closeModal()}),$('<button class="px2-btn px2-btn--primary">').text("OK")],form:{submit:function(){px2style.closeModal();var n=e.find("[name=dirname]").val();n&&t(n)}},width:460},function(){e.find("[name=dirname]").focus()})},mkfile:function(n,t){px2style.loading();var e=$("<div>").html($("#template-mkfile").html());new Promise(function(n){n()}).then(function(){return new Promise(function(t,o){e.find(".cont_current_dir").text(n),e.find("[name=filename]").on("change keyup",function(){e.find("[name=filename]").val();e.find(".cont_html_ext_option").hide()}),t()})}).then(function(){return new Promise(function(n,o){px2style.closeLoading(),px2style.modal({title:"Create new File",body:e,buttons:[$('<button type="button" class="px2-btn">').text("Cancel").on("click",function(n){px2style.closeModal()}),$('<button class="px2-btn px2-btn--primary">').text("OK")],form:{submit:function(){px2style.closeModal();var n=e.find("[name=filename]").val();n&&(px2style.loading(),new Promise(function(n){n()}).then(function(){return new Promise(function(e,o){px2style.closeLoading(),t(n),e()})}))}},width:460},function(){e.find("[name=filename]").focus()}),n()})})},copy:function(n,e){px2style.loading(),new Promise(function(n){n()}).then(function(){return new Promise(function(e,o){t("is_file",n,{},function(n){n.result,e()})})}).then(function(){return new Promise(function(t,o){var i=$("<div>").html($("#template-copy").html());i.find(".cont_target_item").text(n),i.find("[name=copy_to]").val(n),i.find(".cont_contents_option").hide(),px2style.closeLoading(),px2style.modal({title:"Copy",body:i,buttons:[$('<button type="button" class="px2-btn">').text("Cancel").on("click",function(n){px2style.closeModal()}),$('<button class="px2-btn px2-btn--primary">').text("複製する")],form:{submit:function(){px2style.closeModal();var t=i.find("[name=copy_to]").val();t&&t!=n&&(px2style.loading(),new Promise(function(n){n()}).then(function(){return new Promise(function(o,i){px2style.closeLoading(),e(n,t),o()})}))}},width:460},function(){i.find("[name=copy_to]").focus()}),t()})})},rename:function(n,e){px2style.loading(),new Promise(function(n){n()}).then(function(){return new Promise(function(e,o){t("is_file",n,{},function(n){n.result,e()})})}).then(function(){return new Promise(function(t,o){var i=$("<div>").html($("#template-rename").html());i.find(".cont_target_item").text(n),i.find("[name=rename_to]").val(n),i.find(".cont_contents_option").hide(),px2style.closeLoading(),px2style.modal({title:"Rename",body:i,buttons:[$('<button type="button" class="px2-btn">').text("Cancel").on("click",function(n){px2style.closeModal()}),$('<button class="px2-btn px2-btn--primary">').text("移動する")],form:{submit:function(){px2style.closeModal();var t=i.find("[name=rename_to]").val();t&&t!=n&&(px2style.loading(),new Promise(function(n){n()}).then(function(){return new Promise(function(o,i){px2style.closeLoading(),e(n,t),o()})}))}},width:460},function(){i.find("[name=rename_to]").focus()}),t()})})},remove:function(n,e){px2style.loading(),new Promise(function(n){n()}).then(function(){return new Promise(function(e,o){t("is_file",n,{},function(n){n.result,e()})})}).then(function(){return new Promise(function(t,o){var i=$("<div>").html($("#template-remove").html());i.find(".cont_target_item").text(n),px2style.closeLoading(),px2style.modal({title:"Remove",body:i,buttons:[$('<button type="button" class="px2-btn">').text("Cancel").on("click",function(n){px2style.closeModal()}),$('<button class="px2-btn px2-btn--danger">').text("削除する")],form:{submit:function(){px2style.closeModal(),px2style.loading(),new Promise(function(n){n()}).then(function(){return new Promise(function(n,t){n()})}).then(function(){return new Promise(function(n,t){px2style.closeLoading(),e(),n()})})}},width:460},function(){}),t()})})}})).init(n,{},function(){console.log("ready.")})})}});