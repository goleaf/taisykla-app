@props([
    'image' => null,
    'name',
    'role',
    'quote',
    'company' => null,
])

<div {{ $attributes->merge(['class' => 'public-testimonial']) }}>
    <div class="public-testimonial__quote">
        <svg class="quote-icon" fill="currentColor" viewBox="0 0 24 24">
            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
        </svg>
        <p>{{ $quote }}</p>
    </div>
    <div class="public-testimonial__author">
        @if($image)
            <img src="{{ $image }}" alt="{{ $name }}" class="public-testimonial__avatar">
        @else
            <div class="public-testimonial__avatar-placeholder">
                {{ collect(explode(' ', $name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') }}
            </div>
        @endif
        <div class="public-testimonial__info">
            <div class="public-testimonial__name">{{ $name }}</div>
            <div class="public-testimonial__role">
                {{ $role }}
                @if($company)
                    <span>at {{ $company }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .public-testimonial {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 32px;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .public-testimonial__quote {
        flex: 1;
        margin-bottom: 24px;
    }

    .public-testimonial__quote .quote-icon {
        width: 32px;
        height: 32px;
        color: #0f766e;
        opacity: 0.3;
        margin-bottom: 16px;
    }

    .public-testimonial__quote p {
        font-size: 16px;
        color: #334155;
        line-height: 1.7;
        margin: 0;
    }

    .public-testimonial__author {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
    }

    .public-testimonial__avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
    }

    .public-testimonial__avatar-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }

    .public-testimonial__name {
        font-weight: 600;
        color: #0f172a;
        font-size: 15px;
    }

    .public-testimonial__role {
        font-size: 13px;
        color: #64748b;
    }

    .public-testimonial__role span {
        color: #94a3b8;
    }
</style>
