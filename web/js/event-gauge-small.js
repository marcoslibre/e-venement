function gauge_small()
{
  $('.sf_admin_list_td_list_gauge, .sf_admin_list_td_list_manifestations_gauges').addClass('small-gauges'); // a trick for CSS to permit classical rendering compatibility
  
  // creating the same HTML context, independently of the original context (list of manifs or list of events)
  if ( $('.sf_admin_list_td_list_gauge').length > 0 )
  {
    $('.sf_admin_list_td_list_gauge').each(function(){
      var div = $('<div></div>').addClass('gauge')
        .html($(this).html());
      $(this).html('');
      $(this).append(div);
    });
  }
  
  $('.sf_admin_list_td_list_gauge .gauge:not(.done), .sf_admin_list_td_list_manifestations_gauges .gauge:not(.done)').each(function(){
    // if the gauge is not numeric, let go...
    if ( $(this).find('.total').length == 0 || parseInt($(this).find('.total').html()) == 0 )
    {
      if ( $(this).find('.total').length > 0 )
        $(this).html(
          $('<img></img>')
            .addClass('conflict')
            .prop('src',$('head link:first').prop('href')+'/../../sfDoctrinePlugin/images/tick.png')
            .prop('title' , "Pas de conflit d'usage")
        );
      $(this).addClass('done').addClass('other').removeClass('gauge');
      return true;
    }
    
    var total;
    var booked;
    $(this).find('> *').each(function(){
      // every children except for total
      if ( $(this).hasClass('total') )
        return true;
      
      // ... and except booked which is useless graphically
      if ( $(this).hasClass('booked') )
        return true;
      
      // get back local data
      count = parseInt(count_html = $(this).html().replace('&nbsp;',' '),10);
      total = parseInt(total_html = $(this).closest('.gauge, .sf_admin_list_td_list_gauge').find('.total').html().replace('&nbsp;',' '),10);
      
      // set properties
      $(this)
        .prop('title',count_html+' '+$(this).prop('title')+' / '+total_html)
        .css('width',(count/total*100)+'px');
    });
    
    $(this).prop('title', (total=parseInt($(this).find('.total').html(),10)) - (booked=parseInt($(this).find('.booked').html(),10))+' / '+total_html);
    if ( booked > total )
      $(this).addClass('overbooked');
    $(this).addClass('done');
    $('<span class="txt">'+total_html+'</span>').insertAfter($(this));
  });
}

$(document).ready(function(){
  gauge_small();
  
  // for hypothetical pagination...
  if ( window.list_scroll_end == undefined )
    window.list_scroll_end = new Array()
  window.list_scroll_end[window.list_scroll_end.length] = gauge_small;
  
  // for hypothetical integrated search...
  if ( window.integrated_search_end == undefined )
    window.integrated_search_end = new Array()
  window.integrated_search_end[window.integrated_search_end.length] = gauge_small;
});
