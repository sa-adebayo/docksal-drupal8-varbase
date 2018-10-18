/**
 * CKEditor plugin for split text feature for Paragraphs text fields.
 *
 * @file plugin.js
 */

(function ($, Drupal, drupalSettings, CKEDITOR) {

  'use strict';

  // Temporal object is used to preserve data over ajax requests.
  var tmpObject = {};

  /**
   * Register split text plugin for custom CKEditor.
   *
   * @param {object} editorSettings
   *   CKEditor settings object.
   */
  var registerPlugin = function (editorSettings) {
    // Split text toolbar and plugin should be registered only once.
    if (editorSettings.extraPlugins.indexOf('splittext') !== -1) {
      return;
    }

    // We want to have plugin enabled for all text editors.
    editorSettings.extraPlugins += ',splittext';

    // Split text option should be added as last one in toolbar and preserved
    // there after ajax requests are executed.
    var toolbar = editorSettings.toolbar;
    if (typeof editorSettings._splittextIndex === 'undefined') {
      editorSettings._splittextIndex = toolbar.length - 1;
      toolbar.push('/');
    }

    toolbar[editorSettings._splittextIndex] = {
      name: Drupal.t('Split text'),
      items: ['SplitText']
    };
  };

  /**
   * Register split text plugin for all CKEditors.
   *
   * @type {{attach: attach}}
   */
  Drupal.behaviors.setSplitTextPlugin = {
    attach: function () {
      if (!drupalSettings || !drupalSettings.editor || !drupalSettings.editor.formats) {
        return;
      }

      $.each(drupalSettings.editor.formats, function (editorId, editorInfo) {
        if (editorInfo.editor === 'ckeditor') {
          registerPlugin(editorInfo.editorSettings);
        }
      });
    }
  };

  /**
   * Create new paragraph with same type after one where editor is placed.
   *
   * -------------------------------------------------------------------------*
   * Important Note:
   * This could be provided in future as option where split text could work
   * without any add mode, not just modal.
   * -------------------------------------------------------------------------*
   *
   * @param {object} editor
   *   CKEditor object.
   */

  /*
  var createNewParagraphOverDuplicate = function (editor) {
    var actionButton = $('#' + editor.name).closest('.paragraphs-subform')
      .parent()
      .find('.paragraphs-actions input[name$="_duplicate"]');

    storeTempData(editor, actionButton.attr('name'));

    actionButton.trigger('mousedown');
  };
  */

  /**
   * Create new paragraph with same type after one where editor is placed.
   *
   * @param {object} editor
   *   CKEditor object.
   */
  var createNewParagraphOverModal = function (editor) {
    var $paragraphRow = $('#' + editor.name).closest('.paragraphs-subform').closest('tr');
    var paragraphType = $paragraphRow.find('[data-paragraphs-split-text-type]').attr('data-paragraphs-split-text-type');
    var $deltaField = $paragraphRow.closest('table').siblings().find('input.paragraph-type-add-modal-delta');

    // Stop splitting functionality if add button is disabled or not available.
    var $addButton = $deltaField.siblings('.paragraph-type-add-modal-button');
    if ($addButton.length === 0 || $addButton.is(':disabled')) {
      return;
    }

    // New paragraph is always added after existing one - all post ajax
    // functionality expects that.
    var insertionDelta = $paragraphRow.index() + 1;

    // Add in between buttons doubles number of rows.
    if ($paragraphRow.siblings('.paragraphs-features__add-in-between__row').length !== 0) {
      insertionDelta /= 2;
    }
    $deltaField.val(insertionDelta);

    var paragraphTypeButtonSelector = $deltaField.attr('data-drupal-selector').substr('edit-'.length).replace(/-add-more-add-modal-form-area-add-more-delta$/, '-' + paragraphType + '-add-more').replace(/_/g, '-');
    var $actionButton = $('[data-drupal-selector^="' + paragraphTypeButtonSelector + '"]');

    // Triggering element name is required for proper handling of ajax response.
    storeTempData(editor, $actionButton.attr('name'));

    $actionButton.trigger('mousedown');
  };

  /**
   * Store temporal data required after ajax request is finished.
   *
   * @param {object} editor
   *   CKEditor object.
   * @param {string} triggerElementName
   *   Name of trigger element, required for ajax response handling.
   */
  var storeTempData = function (editor, triggerElementName) {
    var $editorObject = $('#' + editor.name);
    var selection = editor.getSelection();
    var ranges = selection.getRanges();
    var endNode = ranges[0].getBoundaryNodes().endNode;

    // First node that should be selected to cut content should be text type.
    var firstNode = ranges[0].document.getBody().getFirst();
    ranges[0].setStartBefore(firstNode);

    // In order to find the first text node, we have to walk forward searching
    // for first text node.
    var walker = new CKEDITOR.dom.walker(ranges[0]);
    var firstTextNode = walker.next();
    while (firstTextNode && firstTextNode.type !== CKEDITOR.NODE_TEXT) {
      firstTextNode = walker.next();
    }

    // To have styles nicely transferred additional tweaks for selection range
    // are required. Only problematic part is when first element is split.
    if (firstTextNode) {
      var firstTextBaseParent = firstTextNode.getParents()[2];
      var endNodeBaseParent = endNode.getParents()[2];
      if (!firstTextBaseParent || !endNodeBaseParent || firstTextBaseParent.equals(endNodeBaseParent)) {
        ranges[0].setStartBefore(firstTextNode);
      }
    }

    // Set new selection and trigger cut for it.
    selection.selectRanges(ranges);

    // First we "cut" text that will be "pasted" to new added paragraph.
    tmpObject.oldContent = editor.extractSelectedHtml(true, true);
    tmpObject.newContent = editor.getData();

    // Set extracted old data back to editor, because flickering of text
    // content in CKEditor looks strange and confusing for user.
    editor.setData(tmpObject.oldContent);

    // Temporal container is used to preserve data over ajax requests.
    tmpObject.originalEditorSelector = $editorObject.data('drupal-selector');

    // Triggering element is required for proper handling of ajax response.
    tmpObject.triggeringElementName = triggerElementName;

    tmpObject.split_trigger = true;
  };

  /**
   * Handler for ajax requests.
   *
   * It handles updating of editors are new paragraph is added.
   *
   * @param {object} e
   *   Event object.
   * @param {object} xhr
   *   XHR object.
   * @param {object} settings
   *   Request settings.
   */
  var onAjaxSplit = function (e, xhr, settings) {
    // Only relevant ajax actions should be handled.
    if (settings.extraData._triggering_element_name !== tmpObject.triggeringElementName || !tmpObject.split_trigger) {
      return;
    }

    // Set relevant data to original paragraph.
    var $originalEditor = $('[data-drupal-selector="' + tmpObject.originalEditorSelector + '"]');
    var originalEditor = CKEDITOR.instances[$originalEditor.attr('id')];
    var $originalRow = $originalEditor.closest('tr');
    updateEditor($originalEditor.attr('id'), tmpObject.oldContent);

    // Set "cut" data ot new paragraph.
    var $newRow = $originalRow.nextAll($originalRow.hasClass('odd') ? '.even' : '.odd').first();
    var wrapperSelector = getEditorWrapperSelector(originalEditor);

    // Build regex for search.
    var fieldSelector = wrapperSelector.replace(/-[0-9]+-/, '-[0-9]+-');
    var $newEditor = $('[data-drupal-selector]', $newRow).filter(function (index) {
      return $(this).data('drupal-selector').match(fieldSelector);
    }).find('textarea');
    updateEditor($newEditor.attr('id'), tmpObject.newContent);

    // Cleanup states.
    tmpObject.split_trigger = false;

    // Delta field has to be cleaned up for proper working of add button. It
    // will not make any impact on non modal add mode.
    $originalRow.closest('table').siblings().find('input.paragraph-type-add-modal-delta').val('');
  };

  /**
   * Helper function to update content of CKEditor.
   *
   * @param {string} editorId
   *   Editor ID.
   * @param {string} data
   *   HTML as text for CKEditor.
   */
  var updateEditor = function (editorId, data) {
    if (typeof editorId === 'undefined') {
      return;
    }

    CKEDITOR.instances[editorId].setData(data, {
      callback: function () {
        this.updateElement();
        this.element.data('editor-value-is-changed', true);
      }
    });
  };

  /**
   * Makes split of paragraph text on cursor position.
   *
   * @param {object} editor
   *   CKEditor object.
   */
  var splitTextHandler = function (editor) {
    // There should be only one split request at a time.
    if (tmpObject.split_trigger) {
      return;
    }

    // After ajax response correct values should be placed in text editors.
    $(document).once('ajax-paragraph').ajaxComplete(onAjaxSplit);

    createNewParagraphOverModal(editor);
  };

  /**
   * Get wrapper Drupal selector for CKEditor.
   *
   * @param {object} editor
   *   CKEditor object.
   *
   * @return {string}
   *   Returns CKEditor wrapper ID.
   */
  var getEditorWrapperSelector = function (editor) {
    return editor.element.getAttribute('data-drupal-selector').replace(/-[0-9]+-value$/, '-wrapper');
  };

  /**
   * Verify if field is direct field of paragraph with enabled split text.
   *
   * Solution is to check that text field wrapper id direct child of subform.
   * And additionally that Wrapper ID is in list of enabled widgets.
   *
   * @param {object} editor
   *   CKEditor object.
   *
   * @return {boolean}
   *   Returns if editor is for valid paragraphs text field.
   */
  var isValidParagraphsField = function (editor) {
    var wrapperSelector = getEditorWrapperSelector(editor);
    var $subForm = $('#' + editor.name).closest('.paragraphs-subform');

    // Paragraphs split text should work only on widgets where that option is enabled.
    var paragraphWrapperId = $subForm.closest('.paragraphs-tabs-wrapper').attr('id');
    if (!drupalSettings.paragraphs_features.split_text[paragraphWrapperId]) {
      return false;
    }

    return $subForm.find('> div[data-drupal-selector="' + wrapperSelector + '"]').length === 1;
  };

  /**
   * Register define new plugin.
   */
  CKEDITOR.plugins.add('splittext', {
    hidpi: true,
    requires: '',

    init: function (editor) {
      // Split text namespace.
      var modulePath = drupalSettings.paragraphs_features.split_text._path;

      // Split Text functionality should be added only for paragraphs Text fields.
      if (!isValidParagraphsField(editor)) {
        return;
      }

      editor.addCommand('splitText', {
        exec: function (editor) {
          splitTextHandler(editor, 'before');
        }
      });

      editor.ui.addButton('SplitText', {
        label: 'Split Text',
        icon: '/' + modulePath + '/js/plugins/splittext/icons/splittext.png',
        command: 'splitText'
      });

      if (editor.addMenuItems) {
        editor.addMenuGroup('splittext');
        editor.addMenuItems({
          splittext: {
            label: Drupal.t('Split Text'),
            command: 'splitText',
            icon: '/' + modulePath + '/js/plugins/splittext/icons/splittext.png',
            group: 'splittext',
            order: 1
          }
        });
      }

      if (editor.contextMenu) {
        editor.contextMenu.addListener(function () {
          return {
            splittext: CKEDITOR.TRISTATE_OFF
          };
        });
      }
    }
  });

}(jQuery, Drupal, drupalSettings, CKEDITOR));
