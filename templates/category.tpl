{extends file='layout.tpl'}

{block name=content}
    <h1>{$category->name}</h1>

    {if $category->description}
        <p class="category-description">{$category->description}</p>
    {/if}

    {include file='partials/sort.tpl'}

    {if $articles}
        <ul class="article-list">
            {foreach $articles as $article}
                <li class="article-item">
                    {if $article->image}
                        <img src="{$article->image}" alt="{$article->title}">
                    {else}
                        <img src="/assets/img/placeholder.png" alt="{$article->title}">
                    {/if}
                    <h2>
                        <a href="/article/{$article->slug}">{$article->title}</a>
                    </h2>
                    <time datetime="{$article->createdAt}">{$article->createdAt|date_format:'%d.%m.%Y'}</time>
                    <span class="views">Просмотров: {$article->views}</span>
                    {if $article->description}
                        <p class="article-description">{$article->description}</p>
                    {/if}
                </li>
            {/foreach}
        </ul>

        {include file='partials/pagination.tpl'}
    {else}
        <p>В этой категории пока нет статей</p>
    {/if}
{/block}