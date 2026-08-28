{extends file='layout.tpl'}

{block name=content}
    <h1>Главная</h1>

    {if $sections}
        {foreach $sections as $section}
            <section class="category-section">
                <h2>
                    <a href="/category/{$section.category->slug}">{$section.category->name}</a>
                </h2>

                {if $section.category->description}
                    <p class="category-description">{$section.category->description}</p>
                {/if}

                <ul class="article-list">
                    {foreach $section.articles as $article}
                        <li class="article-item">
                            {if $article->image}
                                <img src="{$article->image}" alt="{$article->title}">
                            {else}
                                <img src="/assets/img/placeholder.png" alt="{$article->title}">
                            {/if}
                            <h3>
                                <a href="/article/{$article->slug}">{$article->title}</a>
                            </h3>
                            <time datetime="{$article->createdAt}">{$article->createdAt|date_format:'%d.%m.%Y'}</time>
                            <span class="views">Просмотров: {$article->views}</span>
                        </li>
                    {/foreach}
                </ul>

                <a class="all-articles" href="/category/{$section.category->slug}">Все статьи</a>
            </section>
        {/foreach}

        {include file='partials/pagination.tpl'}
    {else}
        <p>Статей пока нет</p>
    {/if}
{/block}