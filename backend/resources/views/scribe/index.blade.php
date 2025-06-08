<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Crypto Tracker API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "https://backend.ddev.site";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.2.1.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.2.1.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-cryptocurrency-management" class="tocify-header">
                <li class="tocify-item level-1" data-unique="cryptocurrency-management">
                    <a href="#cryptocurrency-management">Cryptocurrency Management</a>
                </li>
                                    <ul id="tocify-subheader-cryptocurrency-management" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="cryptocurrency-management-GETapi-cryptocurrencies">
                                <a href="#cryptocurrency-management-GETapi-cryptocurrencies">Get List of All Cryptocurrencies</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="cryptocurrency-management-GETapi-cryptocurrencies--cryptocurrency-">
                                <a href="#cryptocurrency-management-GETapi-cryptocurrencies--cryptocurrency-">Get Cryptocurrency Details</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="cryptocurrency-management-GETapi-cryptocurrencies-trending">
                                <a href="#cryptocurrency-management-GETapi-cryptocurrencies-trending">Get Trending Cryptocurrencies</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-endpoints" class="tocify-header">
                <li class="tocify-item level-1" data-unique="endpoints">
                    <a href="#endpoints">Endpoints</a>
                </li>
                                    <ul id="tocify-subheader-endpoints" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="endpoints-GETapi-user">
                                <a href="#endpoints-GETapi-user">GET api/user</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-user-actions" class="tocify-header">
                <li class="tocify-item level-1" data-unique="user-actions">
                    <a href="#user-actions">User Actions</a>
                </li>
                                    <ul id="tocify-subheader-user-actions" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="user-actions-POSTapi-cryptocurrencies--cryptocurrency--follow">
                                <a href="#user-actions-POSTapi-cryptocurrencies--cryptocurrency--follow">Follow a Cryptocurrency</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="user-actions-DELETEapi-cryptocurrencies--cryptocurrency--follow">
                                <a href="#user-actions-DELETEapi-cryptocurrencies--cryptocurrency--follow">Unfollow a Cryptocurrency</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="user-actions-GETapi-user-cryptocurrencies">
                                <a href="#user-actions-GETapi-user-cryptocurrencies">Get User's Followed Cryptocurrencies</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: June 8, 2025</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<p>API documentation for the Crypto Tracker application. This API allows users to track cryptocurrency prices, follow/unfollow cryptocurrencies, and get market data.</p>
<aside>
    <strong>Base URL</strong>: <code>https://backend.ddev.site</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>To authenticate requests, include an <strong><code>Authorization</code></strong> header with the value <strong><code>"Bearer {YOUR_AUTH_KEY}"</code></strong>.</p>
<p>All authenticated endpoints are marked with a <code>requires authentication</code> badge in the documentation below.</p>
<p>You can retrieve your token by visiting your dashboard and clicking <b>Generate API token</b>.</p>

        <h1 id="cryptocurrency-management">Cryptocurrency Management</h1>

    

                                <h2 id="cryptocurrency-management-GETapi-cryptocurrencies">Get List of All Cryptocurrencies</h2>

<p>
</p>

<p>Returns a paginated list of all cryptocurrencies ordered by market cap (descending).
Data is cached for 5 minutes to improve performance.</p>

<span id="example-requests-GETapi-cryptocurrencies">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://backend.ddev.site/api/cryptocurrencies" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://backend.ddev.site/api/cryptocurrencies"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-cryptocurrencies">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;data&quot;: [
    {
      &quot;id&quot;: 1,
      &quot;asset_type_id&quot;: 1,
      &quot;symbol&quot;: &quot;BTC&quot;,
      &quot;name&quot;: &quot;Bitcoin&quot;,
      &quot;slug&quot;: &quot;bitcoin&quot;,
      &quot;external_id&quot;: &quot;bitcoin&quot;,
      &quot;current_price&quot;: &quot;50000.00000000&quot;,
      &quot;market_cap&quot;: &quot;1000000000000.00&quot;,
      &quot;volume_24h&quot;: &quot;50000000000.00&quot;,
      &quot;price_change_24h&quot;: &quot;2.50000000&quot;,
      &quot;created_at&quot;: &quot;2023-01-01T00:00:00.000000Z&quot;,
      &quot;updated_at&quot;: &quot;2023-01-01T00:00:00.000000Z&quot;
    },
    ...
  ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-cryptocurrencies" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-cryptocurrencies"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-cryptocurrencies"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-cryptocurrencies" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-cryptocurrencies">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-cryptocurrencies" data-method="GET"
      data-path="api/cryptocurrencies"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-cryptocurrencies', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-cryptocurrencies"
                    onclick="tryItOut('GETapi-cryptocurrencies');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-cryptocurrencies"
                    onclick="cancelTryOut('GETapi-cryptocurrencies');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-cryptocurrencies"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/cryptocurrencies</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-cryptocurrencies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-cryptocurrencies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="cryptocurrency-management-GETapi-cryptocurrencies--cryptocurrency-">Get Cryptocurrency Details</h2>

<p>
</p>

<p>Returns detailed information about a specific cryptocurrency by its slug.
Data is cached for 5 minutes to improve performance.</p>

<span id="example-requests-GETapi-cryptocurrencies--cryptocurrency-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://backend.ddev.site/api/cryptocurrencies/bitcoin" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://backend.ddev.site/api/cryptocurrencies/bitcoin"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-cryptocurrencies--cryptocurrency-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: 1,
    &quot;asset_type_id&quot;: 1,
    &quot;symbol&quot;: &quot;BTC&quot;,
    &quot;name&quot;: &quot;Bitcoin&quot;,
    &quot;slug&quot;: &quot;bitcoin&quot;,
    &quot;external_id&quot;: &quot;bitcoin&quot;,
    &quot;current_price&quot;: &quot;50000.00000000&quot;,
    &quot;market_cap&quot;: &quot;1000000000000.00&quot;,
    &quot;volume_24h&quot;: &quot;50000000000.00&quot;,
    &quot;price_change_24h&quot;: &quot;2.50000000&quot;,
    &quot;created_at&quot;: &quot;2023-01-01T00:00:00.000000Z&quot;,
    &quot;updated_at&quot;: &quot;2023-01-01T00:00:00.000000Z&quot;,
    &quot;asset_type&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;Cryptocurrency&quot;,
        &quot;description&quot;: &quot;Digital or virtual currency that uses cryptography for security&quot;,
        &quot;created_at&quot;: &quot;2023-01-01T00:00:00.000000Z&quot;,
        &quot;updated_at&quot;: &quot;2023-01-01T00:00:00.000000Z&quot;
    },
    &quot;followers&quot;: []
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Cryptocurrency not found&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-cryptocurrencies--cryptocurrency-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-cryptocurrencies--cryptocurrency-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-cryptocurrencies--cryptocurrency-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-cryptocurrencies--cryptocurrency-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-cryptocurrencies--cryptocurrency-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-cryptocurrencies--cryptocurrency-" data-method="GET"
      data-path="api/cryptocurrencies/{cryptocurrency}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-cryptocurrencies--cryptocurrency-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-cryptocurrencies--cryptocurrency-"
                    onclick="tryItOut('GETapi-cryptocurrencies--cryptocurrency-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-cryptocurrencies--cryptocurrency-"
                    onclick="cancelTryOut('GETapi-cryptocurrencies--cryptocurrency-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-cryptocurrencies--cryptocurrency-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/cryptocurrencies/{cryptocurrency}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-cryptocurrencies--cryptocurrency-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-cryptocurrencies--cryptocurrency-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>cryptocurrency</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cryptocurrency"                data-endpoint="GETapi-cryptocurrencies--cryptocurrency-"
               value="bitcoin"
               data-component="url">
    <br>
<p>The slug of the cryptocurrency. Example: <code>bitcoin</code></p>
            </div>
                    </form>

                    <h2 id="cryptocurrency-management-GETapi-cryptocurrencies-trending">Get Trending Cryptocurrencies</h2>

<p>
</p>

<p>Returns a list of currently trending cryptocurrencies based on CoinGecko's trending data.</p>

<span id="example-requests-GETapi-cryptocurrencies-trending">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://backend.ddev.site/api/cryptocurrencies/trending" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://backend.ddev.site/api/cryptocurrencies/trending"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-cryptocurrencies-trending">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;coins&quot;: [
    {
      &quot;item&quot;: {
        &quot;id&quot;: &quot;bitcoin&quot;,
        &quot;coin_id&quot;: 1,
        &quot;name&quot;: &quot;Bitcoin&quot;,
        &quot;symbol&quot;: &quot;btc&quot;,
        &quot;market_cap_rank&quot;: 1,
        &quot;thumb&quot;: &quot;https://assets.coingecko.com/coins/images/1/thumb/bitcoin.png&quot;,
        &quot;small&quot;: &quot;https://assets.coingecko.com/coins/images/1/small/bitcoin.png&quot;,
        &quot;large&quot;: &quot;https://assets.coingecko.com/coins/images/1/large/bitcoin.png&quot;,
        &quot;slug&quot;: &quot;bitcoin&quot;,
        &quot;price_btc&quot;: 1,
        &quot;score&quot;: 0
      }
    },
    ...
  ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-cryptocurrencies-trending" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-cryptocurrencies-trending"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-cryptocurrencies-trending"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-cryptocurrencies-trending" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-cryptocurrencies-trending">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-cryptocurrencies-trending" data-method="GET"
      data-path="api/cryptocurrencies/trending"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-cryptocurrencies-trending', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-cryptocurrencies-trending"
                    onclick="tryItOut('GETapi-cryptocurrencies-trending');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-cryptocurrencies-trending"
                    onclick="cancelTryOut('GETapi-cryptocurrencies-trending');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-cryptocurrencies-trending"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/cryptocurrencies/trending</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-cryptocurrencies-trending"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-cryptocurrencies-trending"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="endpoints">Endpoints</h1>

    

                                <h2 id="endpoints-GETapi-user">GET api/user</h2>

<p>
</p>



<span id="example-requests-GETapi-user">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://backend.ddev.site/api/user" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://backend.ddev.site/api/user"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-user">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-user" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-user"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-user"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-user" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-user">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-user" data-method="GET"
      data-path="api/user"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-user', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-user"
                    onclick="tryItOut('GETapi-user');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-user"
                    onclick="cancelTryOut('GETapi-user');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-user"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/user</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-user"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-user"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                <h1 id="user-actions">User Actions</h1>

    

                                <h2 id="user-actions-POSTapi-cryptocurrencies--cryptocurrency--follow">Follow a Cryptocurrency</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Allows the authenticated user to follow a specific cryptocurrency.
Requires authentication via Bearer token.</p>

<span id="example-requests-POSTapi-cryptocurrencies--cryptocurrency--follow">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "https://backend.ddev.site/api/cryptocurrencies/bitcoin/follow" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://backend.ddev.site/api/cryptocurrencies/bitcoin/follow"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-cryptocurrencies--cryptocurrency--follow">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Successfully followed cryptocurrency&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;No query results for model [App\\Models\\Cryptocurrency]&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-cryptocurrencies--cryptocurrency--follow" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-cryptocurrencies--cryptocurrency--follow"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-cryptocurrencies--cryptocurrency--follow"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-cryptocurrencies--cryptocurrency--follow" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-cryptocurrencies--cryptocurrency--follow">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-cryptocurrencies--cryptocurrency--follow" data-method="POST"
      data-path="api/cryptocurrencies/{cryptocurrency}/follow"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-cryptocurrencies--cryptocurrency--follow', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-cryptocurrencies--cryptocurrency--follow"
                    onclick="tryItOut('POSTapi-cryptocurrencies--cryptocurrency--follow');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-cryptocurrencies--cryptocurrency--follow"
                    onclick="cancelTryOut('POSTapi-cryptocurrencies--cryptocurrency--follow');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-cryptocurrencies--cryptocurrency--follow"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/cryptocurrencies/{cryptocurrency}/follow</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-cryptocurrencies--cryptocurrency--follow"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-cryptocurrencies--cryptocurrency--follow"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-cryptocurrencies--cryptocurrency--follow"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>cryptocurrency</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cryptocurrency"                data-endpoint="POSTapi-cryptocurrencies--cryptocurrency--follow"
               value="bitcoin"
               data-component="url">
    <br>
<p>The slug of the cryptocurrency to follow. Example: <code>bitcoin</code></p>
            </div>
                    </form>

                    <h2 id="user-actions-DELETEapi-cryptocurrencies--cryptocurrency--follow">Unfollow a Cryptocurrency</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Allows the authenticated user to unfollow a specific cryptocurrency.
Requires authentication via Bearer token.</p>

<span id="example-requests-DELETEapi-cryptocurrencies--cryptocurrency--follow">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request DELETE \
    "https://backend.ddev.site/api/cryptocurrencies/bitcoin/follow" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://backend.ddev.site/api/cryptocurrencies/bitcoin/follow"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-DELETEapi-cryptocurrencies--cryptocurrency--follow">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Successfully unfollowed cryptocurrency&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;No query results for model [App\\Models\\Cryptocurrency]&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-DELETEapi-cryptocurrencies--cryptocurrency--follow" hidden>
    <blockquote>Received response<span
                id="execution-response-status-DELETEapi-cryptocurrencies--cryptocurrency--follow"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-DELETEapi-cryptocurrencies--cryptocurrency--follow"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-DELETEapi-cryptocurrencies--cryptocurrency--follow" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-DELETEapi-cryptocurrencies--cryptocurrency--follow">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-DELETEapi-cryptocurrencies--cryptocurrency--follow" data-method="DELETE"
      data-path="api/cryptocurrencies/{cryptocurrency}/follow"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('DELETEapi-cryptocurrencies--cryptocurrency--follow', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-DELETEapi-cryptocurrencies--cryptocurrency--follow"
                    onclick="tryItOut('DELETEapi-cryptocurrencies--cryptocurrency--follow');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-DELETEapi-cryptocurrencies--cryptocurrency--follow"
                    onclick="cancelTryOut('DELETEapi-cryptocurrencies--cryptocurrency--follow');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-DELETEapi-cryptocurrencies--cryptocurrency--follow"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-red">DELETE</small>
            <b><code>api/cryptocurrencies/{cryptocurrency}/follow</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="DELETEapi-cryptocurrencies--cryptocurrency--follow"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="DELETEapi-cryptocurrencies--cryptocurrency--follow"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="DELETEapi-cryptocurrencies--cryptocurrency--follow"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>cryptocurrency</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="cryptocurrency"                data-endpoint="DELETEapi-cryptocurrencies--cryptocurrency--follow"
               value="bitcoin"
               data-component="url">
    <br>
<p>The slug of the cryptocurrency to unfollow. Example: <code>bitcoin</code></p>
            </div>
                    </form>

                    <h2 id="user-actions-GETapi-user-cryptocurrencies">Get User&#039;s Followed Cryptocurrencies</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns a list of all cryptocurrencies that the authenticated user is following.
Requires authentication via Bearer token.</p>

<span id="example-requests-GETapi-user-cryptocurrencies">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "https://backend.ddev.site/api/user/cryptocurrencies" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "https://backend.ddev.site/api/user/cryptocurrencies"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-user-cryptocurrencies">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">[
    {
        &quot;id&quot;: 1,
        &quot;asset_type_id&quot;: 1,
        &quot;symbol&quot;: &quot;BTC&quot;,
        &quot;name&quot;: &quot;Bitcoin&quot;,
        &quot;slug&quot;: &quot;bitcoin&quot;,
        &quot;external_id&quot;: &quot;bitcoin&quot;,
        &quot;current_price&quot;: &quot;50000.00000000&quot;,
        &quot;market_cap&quot;: &quot;1000000000000.00&quot;,
        &quot;volume_24h&quot;: &quot;50000000000.00&quot;,
        &quot;price_change_24h&quot;: &quot;2.50000000&quot;,
        &quot;created_at&quot;: &quot;2023-01-01T00:00:00.000000Z&quot;,
        &quot;updated_at&quot;: &quot;2023-01-01T00:00:00.000000Z&quot;,
        &quot;pivot&quot;: {
            &quot;user_id&quot;: 1,
            &quot;cryptocurrency_id&quot;: 1
        },
        &quot;asset_type&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Cryptocurrency&quot;,
            &quot;description&quot;: &quot;Digital or virtual currency that uses cryptography for security&quot;,
            &quot;created_at&quot;: &quot;2023-01-01T00:00:00.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2023-01-01T00:00:00.000000Z&quot;
        }
    }
]</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-user-cryptocurrencies" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-user-cryptocurrencies"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-user-cryptocurrencies"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-user-cryptocurrencies" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-user-cryptocurrencies">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-user-cryptocurrencies" data-method="GET"
      data-path="api/user/cryptocurrencies"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-user-cryptocurrencies', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-user-cryptocurrencies"
                    onclick="tryItOut('GETapi-user-cryptocurrencies');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-user-cryptocurrencies"
                    onclick="cancelTryOut('GETapi-user-cryptocurrencies');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-user-cryptocurrencies"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/user/cryptocurrencies</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-user-cryptocurrencies"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-user-cryptocurrencies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-user-cryptocurrencies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
