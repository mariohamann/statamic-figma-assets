@extends('statamic::layout')
@section('title', __('Figma Assets'))

@section('content')
    <header class="mb-6">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('utilities.index'),
            'title' => __('Utilities'),
        ])
        <div class="flex items-center justify-between">
            <h1>{{ __('Figma Assets') }}</h1>
        </div>
    </header>

    <ul class="text-sm text-gray mb-6 list-disc ml-6">
        <li>
            <span class="font-semibold text-gray-800 dark:text-dark-150">
                {{ __('Info') }}:
            </span>
            <span class="text-gray dark:text-dark-150">
                {{ __('Get information about the assets in the Figma file before downloading them.') }}
            </span>
        </li>
        <li>
            <span class="font-semibold text-gray-800 dark:text-dark-150">
                {{ __('Reimport') }}:
            </span>
            <span class="text-gray dark:text-dark-150">
                {{ __('Reimport every single asset from the Figma file. Overrides existing ones.') }}
            </span>
        </li>
        <li>
            <span class="font-semibold text-gray-800 dark:text-dark-150">
                {{ __('Import') }}:
            </span>
            <span class="text-gray dark:text-dark-150">
                {{ __('Import only the assets that are not already imported.') }}
            </span>
        </li>
    </ul>

    <div class="card p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Assets Container') }}</th>
                    <th>{{ __('Figma Page') }}</th>
                    <th>{{ __('Figma Frame') }}</th>
                    <th>{{ __('Format') }}</th>
                    <th>{{ __('Scale') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($configs as $index => $manager)
                    <tr class="align-top" data-figma-assets-config-id="{{ $index }}">
                        <td class="font-semibold text-gray-800 dark:text-dark-150">
                            {{ $manager['title'] ?? 'None' }}
                        </td>
                        <td class="text-sm text-gray dark:text-dark-150">
                            <span
                                class="badge-pill-sm {{ empty($manager['assets_container']) ? 'bg-orange text-white' : '' }}">{{ $manager['assets_container'] ?? 'Missing' }}</span>
                        </td>
                        <td class="text-sm text-gray dark:text-dark-150">
                            <span
                                class="badge-pill-sm  {{ empty($manager['page_title']) ? 'bg-orange text-white' : '' }}">{{ $manager['page_title'] ?? 'Missing' }}</span>
                        </td>
                        <td class="text-sm text-gray dark:text-dark-150">
                            @if (isset($manager['frame_title']))
                                <span class="badge-pill-sm">{{ $manager['frame_title'] }}</span>
                            @endif
                        </td>
                        <td class="text-sm text-gray dark:text-dark-150">
                            <span class="badge-pill-sm">{{ $manager['format'] ?? 'Missing' }}</span>
                        </td>
                        <td class="text-sm text-gray dark:text-dark-150">
                            <span class="badge-pill-sm">{{ $manager['scale'] ?? 'Missing' }}</span>
                        </td>
                        <td class="rtl:text-left ltr:text-right whitespace-nowrap relative">
                            <div class="figma-assets-progress absolute left-0" hidden
                                data-progress-url="{{ cp_route('utilities.figma-assets.progress', $index) }}">
                                <div class="loader loader-xs"></div>
                                <span class="text-sm text-gray dark:text-dark-150">
                                    {{ __('Loading...') }}
                                </span>
                            </div>
                            <div class="figma-assets-forms flex gap-1 justify-end">
                                <form method="POST" action="{{ cp_route('utilities.figma-assets.info', $index) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-xs">{{ __('Info') }}</button>
                                </form>
                                <form method="POST" action="{{ cp_route('utilities.figma-assets.reimport', $index) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-xs">{{ __('Reimport') }}</button>
                                </form>
                                <form method="POST" action="{{ cp_route('utilities.figma-assets.import', $index) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary btn-xs">{{ __('Import') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const table = document.querySelector('table.data-table');

                table.addEventListener('click', function(event) {
                    const button = event.target.closest('.figma-assets-forms button');
                    if (!button) return;

                    const td = button.closest('td');
                    const row = td.closest('tr');
                    const configId = row.dataset.figmaAssetsConfigId;

                    const forms = td.querySelector('.figma-assets-forms');
                    const progress = td.querySelector('.figma-assets-progress');
                    const progressUrl = progress.dataset.progressUrl;

                    // Prevent double polling if one is already active
                    if (progress.dataset.polling === 'true') return;

                    // Hide forms, show progress
                    forms.style.visibility = 'hidden';
                    progress.hidden = false;
                    progress.dataset.polling = 'true';

                    const poll = () => {
                        fetch(progressUrl, {
                                method: 'GET',
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.message) {
                                    progress.textContent = data.message;
                                } else {
                                    // Done
                                    clearInterval(interval);
                                    forms.style.visibility = '';
                                    progress.hidden = true;
                                    progress.textContent = '';
                                    progress.dataset.polling = 'false';
                                }
                            })
                            .catch(err => {
                                console.error(err);

                                // Check if error is due to background navigation in Safari
                                if (err.message.includes('Load failed')) {
                                    console.log(
                                        'Polling stopped due to background navigation issue in Safari.'
                                    );

                                    // Stop the polling if the error is related to Safari
                                    clearInterval(interval);
                                    progress.textContent = 'Polling stopped due to a navigation issue.';
                                    forms.style.visibility = '';
                                    progress.hidden = true;
                                    progress.dataset.polling = 'false';
                                }
                            });
                    };

                    // Start polling
                    poll(); // Call once immediately
                    const interval = setInterval(poll, 1500);
                });
            });
        </script>
    </div>
@stop
