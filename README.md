# doi_url_updater
This tool updates DOIs hosted at DataCite.

# How to use this tool?
1. Copy the script to an server with PHP (tested: PHP 5.5/5.6)
2. edit updateDOIs.php to your needs ($USER/$PASS are the DataCite account data, set $PROXY if you are behind a proxy)
3. Run it:
`php updateDOIs.php`
4. If you are sure that everything works **(verify output!)** then set `$prod = false` to `$prod = true`!
4. That's it!

If you have any questions: Create an issue.
