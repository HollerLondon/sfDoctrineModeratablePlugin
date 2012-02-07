<?php // include MooTools in view.yml ?>

<script type="text/javascript">
// for recording whether anything has been moderated or not
// on the view comments listing.
var MODERATION_CHANGE = false;

var MODERATION_COLOURS = {
  'unmoderated' : {
    'background-color' : '#CCC',
    'color' : '#000'
  },
  'safe' : {
    'background-color' : '#CCFFB2',
    'color' : '#000'
  },
  'flagged' : {
    'background-color' : '#FF8566',
    'color' : '#000'
  },
  'followup' : {
    'background-color' : '#fff',
    'color' : '#000'
  },
  'rejected' : {
    'background-color' : '#000',
    'color' : '#fff'
  },
};

var colourRows = function()
{
  // comment status coloring
  $$("input[type=radio][name!=global]:checked").each(function(el) {
    el.getParent("tr").setStyles(MODERATION_COLOURS[el.get('value')]);
  });
};

var confirmNoSave = function (event)
{ 
  if (MODERATION_CHANGE) return 'Your moderation changes will be lost.';
};

$(document).addEvent('domready', function()
{
  window.onbeforeunload = confirmNoSave; // doesn't work with addEvent in all browsers
  
  $('save-changes').addEvent('click', function()
  {
    window.onbeforeunload = null;
  });
  
  // change single comment status
  $('moderation-list').getElements('input[type=radio][name!=global]').addEvent('click', function()
  {
    $$('input[name=global]').set('checked', false);
    MODERATION_CHANGE = true;
    colourRows();
  });
  
  // global change, all comments on page
  $$('input[name=global]').addEvent('click', function()
  {
    $$("input[type=radio][value='"+this.get('value')+"'][name!=global]").set('checked', true);
        
    colourRows();
  });
  
  colourRows();

});
</script>