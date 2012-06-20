<?php use_javascript('http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js') ?>

<script>
  // for recording whether anything has been moderated or not
  // on the view comments listing.
  MODERATION_CHANGE = false;

  function colourRows()
  {
    // comment status coloring
    $(":radio[value='unmoderated']:checked").closest("tr").css('backgroundColor', '#fff').css('color', '#000');
    $(":radio[value='safe']:checked").closest("tr").css('backgroundColor', '#CCFFB2').css('color', '#000');
    $(":radio[value='flagged']:checked").closest("tr").css('backgroundColor', '#FF8566').css('color', '#000');
    $(":radio[value='followup']:checked").closest("tr").css('backgroundColor', '#ccc').css('color', '#000');
    $(":radio[value='rejected']:checked").closest("tr").css('backgroundColor', '#000').css('color','#fff');
  }

$(document).ready(function()
{
    window.onbeforeunload = confirmNoSave;

    function confirmNoSave(event)
    { 
        if(!MODERATION_CHANGE)
        {
            event.cancelBubble();
        }
        else
        {
            return "Your moderation changes will be lost.";
        }
    }

    $("#save-changes").click(function()
    {
        window.onbeforeunload = null;
    });
    
    // change single comment status
    $("#moderation-list :radio").not("[name='global']").click(function()
    {
        $(":radio[name='global']").attr('checked', false);
        MODERATION_CHANGE = true;
        colourRows();
    });
    
    // global change, all comments on page
    $(":radio[name='global']").click(function()
    {
        $(":radio[value='"+$(this).val()+"']")
            .not("[name='global']")
            .attr('checked', true);
            
        colourRows();
    });
    
    colourRows();
});
</script>
