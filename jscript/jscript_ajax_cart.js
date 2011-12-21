/* -----------------------------------------------------------------------------------------
   $Id: jscript_ajax_cart.js 899 2007-06-30 20:14:56 VaM $   

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2006	 Andrew Weretennikoff (ajax_sc.js,v 1.1 2007/03/17); medreces@yandex.ru 

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

SHOW_ADDED = 1; // set 0 if you no need show
Offset_X = -10;
Offset_Y = -30;

function addHandler(object, event, handler) { // Thanks xpoint.ru!
  if (typeof object.addEventListener != 'undefined')
    object.addEventListener(event, handler, false);
  else if (typeof object.attachEvent != 'undefined')
    object.attachEvent('on' + event, handler);
  else {
    var handlersProp = '_handlerStack_' + event;
    var eventProp = 'on' + event;
    if (typeof object[handlersProp] == 'undefined') {
      object[handlersProp] = [];
      if (typeof object[eventProp] != 'undefined')
        object[handlersProp].push(object[eventProp]);
      object[eventProp] = function(e) {
        var ret = true;
        for (var i = 0; ret != false && i < object[handlersProp].length; i++)
          ret = object[handlersProp][i](e);
        return ret;
    } }
    object[handlersProp].push(handler);
} }
function removeHandler(object, event, handler) { // Thanks xpoint.ru!
  if (typeof object.removeEventListener != 'undefined')
    object.removeEventListener(event, handler, false);
  else if (typeof object.detachEvent != 'undefined')
    object.detachEvent('on' + event, handler);
  else {
    var handlersProp = '_handlerStack_' + event;
    if (typeof object[handlersProp] != 'undefined') {
      for (var i = 0; i < object[handlersProp].length; i++) {
        if (object[handlersProp][i] == handler) {
          object[handlersProp].splice(i, 1);
          return;
} } } } }

var x, y;
var loadingImage = new Image();
var okImage = new Image();
loadingImage.src = "images/loading.gif";
okImage.src = "images/ok.gif";

if (window.opera || (navigator.userAgent.indexOf('MSIE') > -1)) { //IE + Opera
  getM_x = function () { return event.pageX || (event.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft)) || 0;}
  getM_y = function () { return event.pageY || (event.clientY + (document.documentElement.scrollTop || document.body.scrollTop)) || 0;}
} else { // Mozilla
  addHandler(document, 'mousemove', function(e) {
    x = e.pageX;
    y = e.pageY;
  });
  getM_x = function () { return x; }
  getM_y = function () { return y; }
}
function showOk() {
  var imgLoading = document.getElementById("_loading_");
  with (imgLoading) {
    src = okImage.src;
    style.visibility = "visible";
} }

function hideOk() {
  if(document.getElementById("_loading_")) document.getElementById('_loading_').style.visibility = "hidden";
  removeHandler(document, 'mousemove', hideOk);
}
function showLoading() {
  var imgLoading = document.getElementById("_loading_");
  if(!imgLoading) {
    imgLoading = document.createElement("img");
    with(imgLoading) {
      id = "_loading_";
      style.position = "absolute";
      style.visibility = "hidden";
    }
    document.body.appendChild(imgLoading);
  }
  with(imgLoading) {
    src = loadingImage.src;
    style.left = (getM_x() + Offset_X) + "px";
    style.top = (getM_y() + Offset_Y) + "px";
    style.visibility = "visible";
} }
function hideLoading() {
  if(document.getElementById("_loading_")) document.getElementById("_loading_").style.visibility = "hidden";
}

function doBuyNowGet( link ) {
  showLoading();
  var reqAddCart = new JsHttpRequest();
  reqAddCart.onreadystatechange = function() {
    if (reqAddCart.readyState == 4) {
      if (reqAddCart.responseJS) {
        document.location.href = reqAddCart.responseJS.ajax_redirect;
        return;
      }
      else {
        document.getElementById('divShoppingCart').innerHTML = ''+(reqAddCart.responseText||'')+'';
        hideLoading();
        if ( SHOW_ADDED ) {
          showOk();
          timerID = setTimeout( "addHandler(document, \'mousemove\', hideOk)", 500);
        }
      }
    }
  }
  reqAddCart.caching = false;
  reqAddCart.open('GET', link, true);
  reqAddCart.send(null);
}

function doAddProduct(form) {
  showLoading();
  var reqAddCart = new JsHttpRequest();
  reqAddCart.onreadystatechange = function() {
    if (reqAddCart.readyState == 4) {
      if (reqAddCart.responseJS) {
        document.location.href = reqAddCart.responseJS.ajax_redirect;
        return;
      }
      else {
        document.getElementById('divShoppingCart').innerHTML = ''+(reqAddCart.responseText||'')+''
        if ( SHOW_ADDED ) {
          showOk();
          timerID = setTimeout( "addHandler(document, \'mousemove\', hideOk)", 500);
        }
      }
    }
  }
// собираем все элементы формы:
  var senddata = new Object();
  var fe = form.elements;
  for(var i=0 ; i<fe.length ; i++) {
    if ( fe[i].type=="radio" || fe[i].type=="checkbox" ) {
      if ( fe[i].checked ) senddata[fe[i].name] = fe[i].value;
    } else {
      senddata[fe[i].name] = fe[i].value;
    }
  }
  var url = 'ajax_shopping_cart.php?' + ( senddata.products_id ? 'products_id='+senddata.products_id+'&' : "" ) + ( senddata.products_qty ? '&products_qty='+senddata.products_qty+'&' : "" ) + ( senddata.id ? '&id='+senddata.id+'&' : "" ) + 'action=add_product';

  reqAddCart.caching = false;
  reqAddCart.open( 'GET', url, true);
  reqAddCart.send( senddata );
  return false;
}

function doDelProduct(form) {
  showLoading();
  var reqAddCart = new JsHttpRequest();
  reqAddCart.onreadystatechange = function() {
    if (reqAddCart.readyState == 4) {
      if (reqAddCart.responseJS) {
        document.location.href = reqAddCart.responseJS.ajax_redirect;
        return;
      }
      else {
        document.getElementById('divShoppingCart').innerHTML = ''+(reqAddCart.responseText||'')+''
        if ( SHOW_ADDED ) {
          showOk();
          timerID = setTimeout( "addHandler(document, \'mousemove\', hideOk)", 500);
        }
      }
    }
  }

  var senddata = new Object();
  var fe = form.elements;
  for(var i=0 ; i<fe.length ; i++) {

    if ( fe[i].type=="hidden" ) {
      senddata[fe[i].name] = fe[i].value;
    }
  }
  var url = 'ajax_shopping_cart.php?' + ( senddata["products_id[]"] ? 'products_id[]='+senddata["products_id[]"]+'&' : "" ) + ( senddata["old_qty[]"] ? '&old_qty[]='+senddata["old_qty[]"]+'&' : "" ) + ( senddata["cart_quantity[]"] ? '&cart_quantity[]='+senddata["cart_quantity[]"]+'&' : "" ) + ( senddata["cart_delete[]"] ? '&cart_delete[]='+senddata["cart_delete[]"]+'&' : "" ) + 'action=update_product';

  reqAddCart.caching = false;
  reqAddCart.open( 'GET', url, true);
  reqAddCart.send( senddata );
  return false;
}

function ReviewsPage(page,products_id) {
  showLoading();
  var reqAddCart = new JsHttpRequest();
  reqAddCart.onreadystatechange = function() {
    if (reqAddCart.readyState == 4) {
      if (reqAddCart.responseJS) {
        document.location.href = reqAddCart.responseJS.ajax_redirect;
        return;
      }
      else {
        document.getElementById('divReviews').innerHTML = ''+(reqAddCart.responseText||'')+''
		
        if ( SHOW_ADDED ) {
          showOk();
          timerID = setTimeout( "addHandler(document, \'mousemove\', hideOk)", 500);
        }
      }
    }
  }

  var senddata = new Object();
  var url = 'ajax_reviews_page.php?page=' + page + '&products_id=' + products_id + '&type=ajax';

  reqAddCart.caching = false;
  reqAddCart.open( 'GET', url, true);
  reqAddCart.send( senddata );
  return false;
}
