!function(n){var t={};function e(o){if(t[o])return t[o].exports;var i=t[o]={i:o,l:!1,exports:{}};return n[o].call(i.exports,i,i.exports,e),i.l=!0,i.exports}e.m=n,e.c=t,e.d=function(n,t,o){e.o(n,t)||Object.defineProperty(n,t,{configurable:!1,enumerable:!0,get:o})},e.n=function(n){var t=n&&n.__esModule?function(){return n.default}:function(){return n};return e.d(t,"a",t),t},e.o=function(n,t){return Object.prototype.hasOwnProperty.call(n,t)},e.p="/",e(e.s=322)}({322:function(n,t,e){n.exports=e(323)},323:function(n,t){$(window).on("load",function(){var n=window.filename||"/";function t(n,t){t=t||function(){},$.ajax({type:"get",url:window.contApiParsePx2FilePathEndpoint,headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},contentType:"application/json",dataType:"json",data:{path:n},success:function(n){t(n.pxExternalPath,n.pathFiles,n.pathType)}})}function e(n,t,e,o){o=o||function(){},$.ajax({type:"post",url:window.contCommonFileEditorGpiEndpoint,headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},contentType:"application/json",dataType:"json",data:JSON.stringify({method:n,filename:t,to:e.to,px_command:e.px_command,bin:e.bin}),success:function(n){o(n)}})}(window.remoteFinder=new RemoteFinder(document.getElementById("cont-finder"),{gpiBridge:function(n,t){px2style.loading(),$.ajax({type:"post",url:window.contRemoteFinderGpiEndpoint,headers:{"X-CSRF-TOKEN":$('meta[name="csrf-token"]').attr("content")},contentType:"application/json",dataType:"json",data:JSON.stringify({data:JSON.stringify(n)}),success:function(n){px2style.closeLoading(),t(n)}})},open:function(n,e){switch(px2style.loading(),n.ext){case"html":case"htm":t(n.path,function(t,e,o){console.log(t,o);var i="about:blank";i="contents"==o?window.contContentsEditorEndpoint+"?page_path="+encodeURIComponent(t):window.contCommonFileEditorEndpoint+"?filename="+encodeURIComponent(n.path),window.open(i)});break;default:var o=window.contCommonFileEditorEndpoint+"?filename="+encodeURIComponent(n.path);window.open(o)}px2style.closeLoading(),e(!0)},mkdir:function(n,t){var e=$("<div>").html($("#template-mkdir").html());e.find(".cont_current_dir").text(n),e.find("[name=dirname]").on("change keyup",function(){e.find("[name=dirname]").val().match(/\.html?$/i)?e.find(".cont_html_ext_option").show():e.find(".cont_html_ext_option").hide()}),px2style.modal({title:"Create new Directory",body:e,buttons:[$('<button type="button" class="px2-btn">').text("Cancel").on("click",function(n){px2style.closeModal()}),$('<button class="px2-btn px2-btn--primary">').text("OK")],form:{submit:function(){px2style.closeModal();var n=e.find("[name=dirname]").val();n&&t(n)}},width:460},function(){e.find("[name=dirname]").focus()})},mkfile:function(n,o){px2style.loading();var i,c,a,l=$("<div>").html($("#template-mkfile").html());new Promise(function(n){n()}).then(function(){return new Promise(function(o,a){t(n+"___before.html",function(n,t,a){c=a,(i=n)&&"contents"==c?e("px_command",i,{px_command:"px2dthelper.get.all"},function(n){n.result,o()}):o()})})}).then(function(){return new Promise(function(t,e){l.find(".cont_current_dir").text(n),l.find("[name=filename]").on("change keyup",function(){var n=l.find("[name=filename]").val();i&&"contents"==c&&n.match(/\.html?$/i)?l.find(".cont_html_ext_option").show():l.find(".cont_html_ext_option").hide()}),t()})}).then(function(){return new Promise(function(s,r){px2style.closeLoading(),px2style.modal({title:"Create new File",body:l,buttons:[$('<button type="button" class="px2-btn">').text("Cancel").on("click",function(n){px2style.closeModal()}),$('<button class="px2-btn px2-btn--primary">').text("OK")],form:{submit:function(){px2style.closeModal();var s=l.find("[name=filename]").val();s&&(px2style.loading(),new Promise(function(n){n()}).then(function(){return new Promise(function(o,r){i&&"contents"==c&&s.match(/\.html?$/i)&&l.find("[name=is_guieditor]:checked").val()?t(n+s,function(n,t,i){(a=n)&&"contents"==i?e("px_command",a,{px_command:"px2dthelper.get.all"},function(n){n.result,e("initialize_data_dir",a,{},function(n){o()})}):o()}):o()})}).then(function(){return new Promise(function(n,t){px2style.closeLoading(),o(s),n()})}))}},width:460},function(){l.find("[name=filename]").focus()}),s()})})},copy:function(n,o){var i,c,a,l,s;px2style.loading(),new Promise(function(n){n()}).then(function(){return new Promise(function(t,o){e("is_file",n,{},function(n){i=n.result,t()})})}).then(function(){return new Promise(function(o,s){i?t(n,function(n,t,i){a=t,l=i,(c=n)&&"contents"==l?e("px_command",c,{px_command:"px2dthelper.get.all"},function(n){n.result,o()}):o()}):o()})}).then(function(){return new Promise(function(r,u){var f=$("<div>").html($("#template-copy").html());f.find(".cont_target_item").text(n),f.find("[name=copy_to]").val(n),i&&c&&"contents"==l?f.find(".cont_contents_option").show():f.find(".cont_contents_option").hide(),px2style.closeLoading(),px2style.modal({title:"Copy",body:f,buttons:[$('<button type="button" class="px2-btn">').text("Cancel").on("click",function(n){px2style.closeModal()}),$('<button class="px2-btn px2-btn--primary">').text("複製する")],form:{submit:function(){px2style.closeModal();var c=f.find("[name=copy_to]").val();c&&c!=n&&(px2style.loading(),new Promise(function(n){n()}).then(function(){return new Promise(function(n,o){i&&f.find("[name=is_copy_files_too]:checked").val()?t(c,function(t,o,i){t,s=o,i,e("is_dir",a,{},function(t){t.result?e("copy",a,{to:s},function(t){n()}):n()})}):n()})}).then(function(){return new Promise(function(t,e){px2style.closeLoading(),o(n,c),t()})}))}},width:460},function(){f.find("[name=copy_to]").focus()}),r()})})},rename:function(n,o){var i,c,a,l,s;px2style.loading(),new Promise(function(n){n()}).then(function(){return new Promise(function(t,o){e("is_file",n,{},function(n){i=n.result,t()})})}).then(function(){return new Promise(function(e,o){i?t(n,function(n,t,o){c=n,a=t,l=o,e()}):e()})}).then(function(){return new Promise(function(r,u){var f=$("<div>").html($("#template-rename").html());f.find(".cont_target_item").text(n),f.find("[name=rename_to]").val(n),i&&c&&"contents"==l?f.find(".cont_contents_option").show():f.find(".cont_contents_option").hide(),px2style.closeLoading(),px2style.modal({title:"Rename",body:f,buttons:[$('<button type="button" class="px2-btn">').text("Cancel").on("click",function(n){px2style.closeModal()}),$('<button class="px2-btn px2-btn--primary">').text("移動する")],form:{submit:function(){px2style.closeModal();var r=f.find("[name=rename_to]").val();r&&r!=n&&(px2style.loading(),new Promise(function(n){n()}).then(function(){return new Promise(function(n,o){i&&c&&"contents"==l&&f.find("[name=is_rename_files_too]:checked").val()?t(r,function(t,o,i){s=o,t&&"contents"==i?e("is_dir",a,{},function(t){t.result?e("rename",a,{to:s},function(t){n()}):n()}):n()}):n()})}).then(function(){return new Promise(function(t,e){px2style.closeLoading(),o(n,r),t()})}))}},width:460},function(){f.find("[name=rename_to]").focus()}),r()})})},remove:function(n,o){var i,c,a,l;px2style.loading(),new Promise(function(n){n()}).then(function(){return new Promise(function(t,o){e("is_file",n,{},function(n){i=n.result,t()})})}).then(function(){return new Promise(function(o,s){i?t(n,function(n,t,i){a=t,l=i,(c=n)&&"contents"==l?e("px_command",c,{px_command:"px2dthelper.get.all"},function(n){n.result,o()}):o()}):o()})}).then(function(){return new Promise(function(t,s){var r=$("<div>").html($("#template-remove").html());r.find(".cont_target_item").text(n),i&&c&&"contents"==l&&r.find(".cont_contents_option").show(),px2style.closeLoading(),px2style.modal({title:"Remove",body:r,buttons:[$('<button type="button" class="px2-btn">').text("Cancel").on("click",function(n){px2style.closeModal()}),$('<button class="px2-btn px2-btn--danger">').text("削除する")],form:{submit:function(){px2style.closeModal(),px2style.loading(),new Promise(function(n){n()}).then(function(){return new Promise(function(n,t){i&&c&&"contents"==l&&r.find("[name=is_remove_files_too]:checked").val()&&a.length?e("is_dir",a,{},function(t){t.result?e("remove",a,{},function(t){n()}):n()}):n()})}).then(function(){return new Promise(function(n,t){px2style.closeLoading(),o(),n()})})}},width:460},function(){}),t()})})}})).init(n,{},function(){console.log("ready.")})})}});