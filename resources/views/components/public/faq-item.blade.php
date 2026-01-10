@props([
    'question',
    'open' => false,
])

<div class="public-faq-item" x-data="{ open: {{ $open ? 'true' : 'false' }} }">
    <button class="public-faq-item__question" @click="open = !open">
         <span>{{ $question }}</span>
        <svg :class="{ 'rotated': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div class="public-faq-item__answer" x-show="open" x-transition x-cloak>
        {{ $slot }}
    </div>
</div>

<style>
    .public-faq-item {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
    }

    .public-faq-item__question {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        font-size: 15px;
        font-weight: 600;
        color: #0f172a;
        background: none;
        border: none;
        cursor: pointer;
        text-align: left;
        gap: 16px;
    }

    .public-faq-item__question:hover {
        background: #f8fafc;
    }

    .public-faq-item__question svg {
        width: 20px;
        height: 20px;
        color: #64748b;
        transition: transform 0.2s;
        flex-shrink: 0;
    }

    .public-faq-item__question svg.rotated {
        transform: rotate(180deg);
    }

    .public-faq-item__answer {
        padding: 0 24px 20px;
        font-size: 14px;
        color: #64748b;
        line-he
     i  ght: 1.7;

       }

    [x-cloak] { display: none !important; }
</style>
