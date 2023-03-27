# TYPO3 EXT:reduce_duplicate_content

`composer req andersundsehr/reduce-duplicate-content`

## What dose this Extension do?

it aims to reduce the URL's there the same content is reachable.

The main point is that by default TYPO3's pages are accessible like this: `/en/page-a` and `/en/page-a/`.

With one simple setting you can change if you always want trailing Slashes or if you never want them.

This setting only sets the way TYPO3 generates the URL's.  
But the content is still accessible by both URL's  

This is there this Extension comes in.

## How dose it solve this Problem:

We Generate a URL for the Current Page and Language.  
If this URL matches the current Request URL (ignoring the Slash at the end.)  
Only than we directly compare the URL's.  
If they do not match we redirect to the correct URL.

## How is it different to studiomitte/redirect2trailingslash

[EXT:redirect2trailingslash](https://github.com/studiomitte/redirect2trailingslash) is only usefull if you always want slashes at the end.  
With our approach it is also possible to remove the slashes.  
Also it should have way less errors because of the way we compare it with the generated URL's.

## If you want Trailing Slashes:

you can configure a routeEnhancer like this:  
file: `config/sites/.../config.yaml`
````yml
routeEnhancers:
  PageTypeSuffix:
    type: PageType
    # if you want to have trailing slashes for all pages:
    default: '/'
    index: ''
    map:
      /: 0
      sitemap.xml: 1533906435
````

## If you do not want Trailing Slashes:

you can remove the RouteEnhancer PageType.  
or make sure that you do not use the `default`:  


file: `config/sites/.../config.yaml`
````yml
routeEnhancers:
  PageTypeSuffix:
    type: PageType
    index: ''
    map:
      /: 0
      sitemap.xml: 1533906435
````

## Change the 307 Status Code:

You can change it in the Extension Settings.

# with ♥️ from anders und sehr GmbH

> If something did not work 😮  
> or you appreciate this Extension 🥰 let us know.

> We are hiring https://www.andersundsehr.com/karriere/

