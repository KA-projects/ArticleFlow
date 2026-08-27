{if $totalPages > 1}
    <nav class="pagination">
        {if $page > 1}
            <a href="{$baseUrl}?sort={$sort}&page={$page-1}">&laquo; Назад</a>
        {/if}

        {for $i = 1 to $totalPages}
            {if $i == $page}
                <span class="current">{$i}</span>
            {else}
                <a href="{$baseUrl}?sort={$sort}&page={$i}">{$i}</a>
            {/if}
        {/for}

        {if $page < $totalPages}
            <a href="{$baseUrl}?sort={$sort}&page={$page+1}">Вперёд &raquo;</a>
        {/if}
    </nav>
{/if}
