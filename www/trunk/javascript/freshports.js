<!-- example from http://www.cssnewbie.com/example/showhide-content/ -->
$(document).ready(function() {

  var clickFnExpand = function() {
    var ctl = $($(this).attr('data-control'));

    ctl.toggle()

    var hidden = ctl.is(':hidden');

    $(this).attr('src',   hidden ? '/images/expand.gif' : '/images/contract.gif' );
    $(this).attr('alt',   hidden ? 'Expand depends'     : 'Contract depends' );
    $(this).attr('title', hidden ? 'Expand depends'     : 'Contract depends' );
    return false;
  };

  $('.contract').click( clickFnExpand );
  $('.contract').click();
  $('.showLink').click( clickFnExpand );
  $('.showLink').click();
  
});

