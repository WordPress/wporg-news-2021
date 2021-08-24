<?php

$path = defined( 'WPORG_SANDBOXED' ) && WPORG_SANDBOXED ? 'vendor' : 'foo';

require_once $path . '/components/global-header/header.php';

// on prod, there won't be a vendor dir?
// don't want each theme to have it's own checkout
?>


Er, wait, it looks like the `header.php` and `footer.php` that were forked from blockbase aren't used at all?
then why are they there?

should delete them if theyre not used -
we dont need for i18n b/c rosetta sites wont use this theme
we won't tackle them until FSE supports parent themes and i18n

if we do need them for i18n, we could just have single `includes/strings.php` that has them all w/ no markup


	------

try putting script tag in .hgml templates
works


try iframing the w.org header now
that should work
yes, but not locally b/c of x-frame-options
