  $(document).ready(function(){
    var form = $('#li_transaction_field_price_new form');
    $('#li_transaction_field_price_new').click(function(){
      $(this).find('input[type=text]').focus();
    });
    
    // dealing w/ the GUI for cancellations
    $('#li_transaction_field_price_new .cancel').click(function(){
      var url = $(this).prop('href');
      $(this).prop('href', form.prop('action'));
      form.prop('action', url);
      $('#li_transaction_field_price_new').toggleClass('cancelling').find('form').toggleClass('noajax');
      $('#li_transaction_field_price_new').find('a, input, button').unbind('focusout').focusout(function(){ return false; });
      $('#li_transaction_field_price_new [name="transaction[price_new][qty]"]').focus();
      return false;
    });
    $('.highlight').focusin(function(){
      if ( $('#li_transaction_field_price_new').hasClass('cancelling') )
        $('#li_transaction_field_price_new .cancel').click();
    });
    
    $('#li_transaction_field_content .highlight:not(.new-family)').focusin(function(){
      form.find('button').remove();
      var item = this;
      var available_prices = JSON.parse($.trim($(this).find('.data .available_prices').text()));
      $.each(available_prices, function(i, price){
        $('<button name="price_new[id]"></button>')
          .val(price.id)
          .html(price.name)
          .prop('title', price.value+' - '+price.description)
          .attr('data-gauge-id', $(item).attr('data-gauge-id'))
          .appendTo(form.find('p'))
          .click(function(){
            $(this).closest('form').find('[name="transaction[price_new][price_id]"]')
              .val($(this).val());
            $(this).closest('form').find('[name="transaction[price_new][gauge_id]"]')
              .val($(this).attr('data-gauge-id'));
          })
        ;
      });
      
      $('#li_transaction_field_price_new').fadeIn();
    }).focusout(function(){
      setTimeout(function(){
        if ( $('#li_transaction_field_content .ui-state-highlight').length == 0 )
          $('#li_transaction_field_price_new').fadeOut();
      },100);
    });
  });