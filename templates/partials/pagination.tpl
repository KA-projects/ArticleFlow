{if $totalPages > 1}
    <nav class="pagination">
        {if $page > 1}
            <a href="{$baseUrl}?sort={$sort}&page={$page-1}">&laquo; Назад</a>
        {/if}

        {foreach $pages as $item}
            {if isset($item.ellipsis)}
                <span class="ellipsis">…</span>
            {elseif $item.page == $page}
                <span class="current">{$item.page}</span>
            {else}
                <a href="{$baseUrl}?sort={$sort}&page={$item.page}">{$item.page}</a>
            {/if}
        {/foreach}

        {if $page < $totalPages}
            <a href="{$baseUrl}?sort={$sort}&page={$page+1}">Вперёд &raquo;</a>
        {/if}
    </nav>
{/if}