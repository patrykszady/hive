<div class="max-w-xl mx-auto space-y-4 sm:px-6">
    <x-cards.wrapper>
        <x-lists.ul>
            <x-lists.search_li
                :basic=true
                :bold="TRUE"
                :line_title="'REVENUE'"
                :line_data="money($revenue)"
                >
            </x-lists.search_li>
        </x-lists.ul>
    </x-cards.wrapper>

    <x-cards.wrapper>
        <x-cards.heading>
            <x-slot name="left">
                <h1 class="text-lg">Cost of Revenue</b></h1>
            </x-slot>
        </x-cards.heading>

        <x-cards.body>
            <x-lists.ul>
                <x-lists.search_li
                    :basic=true
                    :line_title="'Cost of Labor'"
                    :line_data="money($cost_of_labor)"
                    >
                </x-lists.search_li>
                <x-lists.search_li
                    :basic=true
                    :bold="TRUE"
                    :line_title="'TOTAL'"
                    :line_data="money($revenue)"
                    >
                </x-lists.search_li>
            </x-lists.ul>
        </x-cards.body>
    </x-cards.wrapper>

    <x-cards.wrapper>
        <x-lists.ul>
            <x-lists.search_li
                :basic=true
                :bold="TRUE"
                :line_title="'GROSS PROFIT'"
                :line_data="money($revenue - $cost_of_labor)"
                >
            </x-lists.search_li>
        </x-lists.ul>
    </x-cards.wrapper>

    <x-cards.wrapper>
        <x-cards.heading>
            <x-slot name="left">
                <h1 class="text-lg">General & Administrative Expenses</b></h1>
            </x-slot>
        </x-cards.heading>

        <x-cards.body>
            <x-lists.ul>
                <x-lists.search_li
                    :basic=true
                    :line_title="'Cost of Labor'"
                    :line_data="money($cost_of_labor)"
                    >
                </x-lists.search_li>
                <x-lists.search_li
                    :basic=true
                    :bold="TRUE"
                    :line_title="'TOTAL'"
                    :line_data="money($revenue)"
                    >
                </x-lists.search_li>
            </x-lists.ul>
        </x-cards.body>
    </x-cards.wrapper>

    <x-cards.wrapper>
        <x-lists.ul>
            <x-lists.search_li
                :basic=true
                :bold="TRUE"
                :line_title="'NET INCOME'"
                :line_data="money($revenue - $cost_of_labor)"
                >
            </x-lists.search_li>
        </x-lists.ul>
    </x-cards.wrapper>
</div>
