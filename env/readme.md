These files are used to provision a local development environment.

The .xml files are exports from the actual wordpress.org/news site so that the theme can be developed with real content.

⚠️ Important: When generating these export files, personal information about post and comment authors needs to be scrubbed.

The following regex can be used to replace author email addresses with dummy ones:

Search: `<wp:author_login><!\[CDATA\[([^\]]+)\]\]></wp:author_login><wp:author_email><!\[CDATA\[[^\]]+\]\]>`
Replace: `<wp:author_login><![CDATA[$1]]></wp:author_login><wp:author_email><![CDATA[$1@example.org]]>`

The following regex can be used to redact comment author information:

Search: `<wp:comment_author_([^>]+)><?[^<>]+>?`
Replace: `<wp:comment_author_$1><!\[CDATA\[$1 redacted\]\]>`
