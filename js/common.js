/*
AV 20211119
Оригинальная версия: 
add_event(document, 'DOMContentLoaded', common.init);
Вызывает ошибку: 
common.js?1:3 Uncaught TypeError: Cannot read properties of undefined (reading 'init')
Версия ниже работает чисто: 
*/
add_event(document, 'DOMContentLoaded', function() {
    common.init();
});

var common = {
  init: function() {
    console.log('you code here ...');
    common.tooltip1();
    common.hide1();
    //common.do_nothing(); эту не будем вызывать при инициализации...
    common.what_device();
  },
  tooltip1: function(id) {
      jQuery( id ).removeClass('vis_off');
      console.log('Показан текст: Общий показатель...');
  },
  hide1: function(id) {
      jQuery( id ).addClass('vis_off');
      console.log('Спрятан текст: Общий показатель...');
  },
  do_nothing: function(info1) {
      console.log('Вызываем функцию do_nothing (общую) ...');
      do_nothing(info1);
  },
  what_device: function() {
      console.log('Пытаемся отпределить, с какого устройства смотрим этот сайт ...');
      let nua = navigator.userAgent;
      let device = {
          iphone: nua.match(/(iPhone|iPod|iPad)/),
          blackberry: nua.match(/BlackBerry/),
          android: nua.match(/Android/)
        };
      if (device.android){
          console.log('Используем Андроид ...');
      }
      else if (device.iphone){
          console.log('Используем iphone ...');
      }
      else if (device.blackberry){
          console.log('Используем blackberry ...');
      }
      else {
          console.log('Используем НЕмобильное устройство ...');
      }
  },
//
} // var common = {
//


jQuery(document).ready(function() {
//
  jQuery( "#dialog_main" ).dialog({ autoOpen: false });
  jQuery( "#dialog_main" ).dialog( "option", "draggable", true );
  jQuery( "#dialog_main" ).dialog( "option", "modal", true );
//
// Визуальные эффекты при загрузки страницы...
//
  jQuery( "#group21090_bizman" ).hide();
  jQuery( "#group21090_group2211" ).hide();
  jQuery( "#group21090_group2210" ).hide();
  jQuery( "#group21090_group2209" ).hide();
  jQuery( "#group21090_el" ).delay(4000).fadeOut(800);
  jQuery( "#group21090_el" ).delay(4000).fadeIn(800);
  jQuery( "#group21090_bizman" ).delay(4000).fadeIn(800);
  jQuery( "#group21090_group2211" ).delay(1000).fadeIn(800);
  jQuery( "#group21090_group2210" ).delay(2000).fadeIn(800);
  jQuery( "#group21090_group2209" ).delay(3000).fadeIn(800);
//
});
//
/*
This is enda of...
jQuery(document).ready(function() {
**************************************************************************************
***** This is The Great Wall (border) between .ready area and after .ready area  *****
**************************************************************************************
*/ 
//



function do_nothing(info1) {
//
// Заглушки при кликах пользователя на разные кнопки... 
  console.log("do_nothing clicked: ", info1);
//
  jQuery("#dialog_main").html(info1);
  jQuery( "#dialog_main" ).dialog( "open" );
  jQuery("#dialog_main").dialog({
    show: {
    effect: 'fade',
    duration: 800,
    },
  });
  jQuery( "#dialog_main" ).dialog( "option", "width", 560 );
  jQuery( "#dialog_main" ).dialog( "option", "draggable", true );
  jQuery( "#dialog_main" ).dialog( "option", "modal", true );
  jQuery( "#dialog_main" ).dialog( "option", "buttons", [ { text: "Понятно...", click: function() { jQuery( this ).dialog( "close" ); } } ] );
//
} // function do_nothing(info1) {
//


/*

This is enda...
of this file

*/



