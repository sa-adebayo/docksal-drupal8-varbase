Mail Editor
===========

This module provides the ability to customize e-mail templates for mail sent
out using drupal_mail().

Users with the 'Administer mail templates' permission may go to
admin/config/system/mail-edit, where they find a list of all email templates
that can be customized. If you use a separate admin theme, then the 'View the
administration theme' permission may be needed, too.


Dependencies
--------------------------------------------------------------------------------
The Mail Edit module does not have any dependencies, but if the Token module is
installed it will show a list of available tokens when editing a message.


Installation
--------------------------------------------------------------------------------
Download and copy the module to the /modules/contrib directory. Enable the
module through the Admin -> Extend (/admin/modules) page.


Developers
--------------------------------------------------------------------------------
See mail_edit.api.php for details on adding support to other modules.
