{extends file='layout.tpl'}

{block name=content}
    <article class="article">
        {if $article.image}
            <img src="{$article.image}" alt="{$article.title}">
        {/if}
        <h1>{$article.title}</h1>

        <p class="article-meta">
            <time datetime="{$article.created_at}">{$article.created_at|date_format:'%d.%m.%Y'}</time>
            <span class="views">{$viewsLabel}</span>
            <span class="categories">
                {foreach $article.categories as $cat}
                    <a href="/category/{$cat.slug}">{$cat.name}</a>
                {/foreach}
            </span>
        </p>

        {if $article.description}
            <p class="article-description">{$article.description}</p>
        {/if}

        <div class="article-text">
            {$article.text}
        </div>
    </article>

    {if $similar}
        <section class="similar">
            <h2>Похожие статьи</h2>
            <ul class="article-list">
                {foreach $similar as $item}
                    <li class="article-item">
                        {if $item.image}
                            <img src="{$item.image}" alt="{$item.title}">
                        {/if}
                        <h3>
                            <a href="/article/{$item.slug}">{$item.title}</a>
                        </h3>
                        <time datetime="{$item.created_at}">{$item.created_at|date_format:'%d.%m.%Y'}</time>
                    </li>
                {/foreach}
            </ul>
        </section>
    {/if}
{/block}