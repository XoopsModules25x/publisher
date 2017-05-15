READ ME FIRST
_____________________________________________________________________

Forked from SmartSection 2.14 as a starting base, but redone somewhat from the ground up to reduce the overall server load and deal with bugs and such.

Features:

- Categories and Subcategories
- Pages
- Four basic templates, custom templates
- File Wrapping
- Page/Category images
- File attachments
- Scheduled publishing and expiration
- Order by date, ratings, sort order
- Ratings
- Comments
- SEO
- Permissions: Submissions, Submit/Edit fields, Categories, Pages, Moderation (global)
- CK Editor and others using XOOPSeditors
- Import from SmartSection and News modules
- Easy cloning (change the directory name)


 REQUIREMENTS
 _____________________________________________________________________

- PHP version >= 5.5
- XOOPS version >= 2.5.8

INSTALLATION
_____________________________________________________________________

1) You install the module as just any other XOOPS module.
Detailed instructions on installing modules are available in the XOOPS Operations Manual:
https://www.gitbook.com/book/xoops/xoops-operations-guide/details"

2) PDF in XOOPS 2.5.8
If you want to use the PDF feature in Publisher, you will need to copy the TCPDF library to your XOOPS folder:

/class/libraries/vendor/

a) create the folders there:

/tecnickcom/tcpdf/

so it looks like:

/class/libraries/vendor/tecnickcom/tcpdf/

b) download the TCPDF library. You have three choices:

 i) download the streamlined XOOPS version from: http://sourceforge.net/projects/chgxoops/files/Frameworks/tcpdf_for_xoops/

 ii) download the latest full release from: https://github.com/tecnickcom/TCPDF/releases

 iii) If you feel comfortable with Composer (https://getcomposer.org/) add this line to your "composer.js" file located in /class/libraries/:

  "tecnickcom/tcpdf":"6.*"

and then run the command:

    composer update

Your PDF should now work.

Enjoy your XOOPS Publisher module!

Your XOOPS Development Team

