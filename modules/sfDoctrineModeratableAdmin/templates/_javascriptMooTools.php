<?php // include MooTools in view.yml ?>

<script type="text/javascript">
// for recording whether anything has been moderated or not
// on the view comments listing.
var MODERATION_CHANGE = false;
var MODERATION_STATES = [ 'unmoderated', 'safe', 'followup', 'rejected', 'flagged' ];

var colourRows = function() {
  // comment status coloring
  $$("input[type=radio][name!=global]:checked").each(function(el) {
    var parentTr          = el.getParent("tr");
    var currentClasses    = parentTr.get('class').split(' '); 
    var potentialModClass = currentClasses.pop();
    
    if (-1 != MODERATION_STATES.indexOf(potentialModClass)) parentTr.removeClass(potentialModClass);
    
    parentTr.addClass(el.get('value'));
  });
};

var confirmNoSave = function (event) { 
  if (MODERATION_CHANGE) return 'Your moderation changes will be lost.';
};

$(document).addEvent('domready', function() {
  window.onbeforeunload = confirmNoSave; // doesn't work with addEvent in all browsers

  if ($('save-changes')) {
    $('save-changes').addEvent('click', function() {
      window.onbeforeunload = null;
    });
  }
  
  // change single comment status
  if ($('moderation-list')) {
    $('moderation-list').getElements('input[type=radio][name!=global]').addEvent('click', function() {
      $$('input[name=global]').set('checked', false);
      MODERATION_CHANGE = true;
      colourRows();
    });
  }
  
  // global change, all comments on page
  $$('input[name=global]').addEvent('click', function() {
    $$("input[type=radio][value='"+this.get('value')+"'][name!=global]").set('checked', true);
        
    colourRows();
  });
  
  colourRows();
});
</script>