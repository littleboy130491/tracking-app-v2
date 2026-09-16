{{--
    File: resources/views/filament/bill-of-ladings/milestone-stepper.blade.php
    Responsibility: Horizontal milestone stepper for the B/L Progress tab.
    What it does:
    - Draws the milestone sequence as dots on a progress line: done = filled,
      current = highlighted ring, upcoming = translucent grey.
    - Each step is a button calling jumpToMilestone() on the edit page;
      wire:confirm guards against misclicks.
    - Styles are scoped in this file (the .bl-ms prefix) because the admin
      panel ships precompiled CSS — app Tailwind classes are not generated.
    Props: $sequence (ShipmentMilestone[]), $current (?ShipmentMilestone),
           $editable (bool — false on the create page, where there is no record).
--}}
<style>
    .bl-ms { overflow-x: auto; padding: 4px 0 6px; }
    .bl-ms ol { display: flex; min-width: max-content; margin: 0; padding: 0; list-style: none; }
    .bl-ms li { position: relative; display: flex; flex-direction: column; align-items: center; width: 108px; flex-shrink: 0; }
    /* connector line: a full-width bar centered behind each dot, joining neighbours */
    .bl-ms li::before { content: ''; position: absolute; top: 11px; left: -50%; width: 100%; height: 2px; background: rgba(148, 163, 184, .35); }
    .bl-ms li:first-child::before { display: none; }
    .bl-ms li.reached::before { background: rgb(3, 235, 98); }
    .bl-ms .ms-btn { display: flex; flex-direction: column; align-items: center; gap: 6px; width: 100%; background: none; border: 0; padding: 0; }
    .bl-ms .ms-btn:not(:disabled) { cursor: pointer; }
    .bl-ms .ms-dot { position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 9999px; font-size: 11px; font-weight: 600; background: rgba(148, 163, 184, .35); color: rgb(100, 116, 139); transition: transform .15s ease, background .15s ease; }
    .bl-ms .ms-btn:not(:disabled):hover .ms-dot { transform: scale(1.15); }
    .bl-ms li.done .ms-dot { background: rgb(3, 235, 98); color: rgb(4, 60, 30); }
    .bl-ms li.current .ms-dot { background: rgb(2, 180, 75); color: #fff; box-shadow: 0 0 0 4px rgba(3, 235, 98, .3); }
    .bl-ms .ms-label { font-size: 11px; line-height: 1.25; color: rgb(100, 116, 139); text-align: center; }
    .bl-ms li.current .ms-label { font-weight: 600; color: rgb(2, 140, 60); }
    .dark .bl-ms li::before { background: rgba(100, 116, 139, .45); }
    .dark .bl-ms li.reached::before { background: rgb(3, 235, 98); }
    .dark .bl-ms .ms-dot { background: rgba(100, 116, 139, .4); color: rgb(203, 213, 225); }
    .dark .bl-ms li.done .ms-dot { background: rgb(3, 235, 98); color: rgb(4, 60, 30); }
    .dark .bl-ms li.current .ms-dot { background: rgb(3, 235, 98); color: rgb(4, 60, 30); }
    .dark .bl-ms .ms-label { color: rgb(148, 163, 184); }
    .dark .bl-ms li.current .ms-label { color: rgb(74, 222, 128); }
</style>
@php
    $currentIndex = $current ? array_search($current, $sequence, true) : false;
    // Forward moves are capped at one step; any earlier step stays clickable.
    $nextAllowedIndex = $currentIndex === false ? 0 : $currentIndex + 1;
@endphp
<div class="bl-ms">
    <ol>
        @foreach ($sequence as $i => $step)
            @php
                $done = $currentIndex !== false && $i < $currentIndex;
                $active = $step === $current;
                $allowed = $editable && ! $active && $i <= $nextAllowedIndex;
                $liClass = $done ? 'done reached' : ($active ? 'current reached' : '');
            @endphp
            <li class="{{ $liClass }}">
                <button
                    type="button"
                    wire:click="mountAction('jumpToMilestone', {'milestone': '{{ $step->value }}'})"
                    @disabled(! $allowed)
                    class="ms-btn"
                >
                    <span class="ms-dot">{{ $done ? '✓' : $i + 1 }}</span>
                    <span class="ms-label">{{ $step->getLabel() }}</span>
                </button>
            </li>
        @endforeach
    </ol>
</div>
