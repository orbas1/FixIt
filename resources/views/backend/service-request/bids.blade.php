@php
    use App\Helpers\Helpers;
    use App\Enums\RoleEnum;
@endphp
@isset($serviceRequest)
    <!-- Button trigger modal -->
    <a href="javascript:void(0)" class="booking-icon show-icon" data-bs-toggle="modal"
        data-bs-target="#bidsModal{{ $serviceRequest->id }}">
        <i data-feather="eye"></i>
    </a>
    <!-- Modal -->
    <div class="modal fade bid-modal" id="bidsModal{{ $serviceRequest->id }}" tabindex="-1"
        aria-labelledby="bidsModalLabel{{ $serviceRequest->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bidsModalLabel{{ $serviceRequest->id }}">{{ __('static.bid.bids') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __('static.service_request.amount') }}</th>
                                <th>{{ __('static.service_request.provider_name') }}</th>
                                <th>{{ __('static.service_request.provider_email') }}</th>
                                <th>{{ __('static.service_request.description') }}</th>
                                <th>{{ __('static.service_request.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $isProvider = Helpers::getCurrentRolename() === RoleEnum::PROVIDER;
                                $currentUserId = auth()->user()->id;
                            @endphp
                            @forelse ($serviceRequest->bids as $bid)
                                @if (!$isProvider || $bid->provider_id == $currentUserId)
                                    <tr>
                                        <td>{{ Helpers::getSettings()['general']['default_currency']->symbol }}{{ $bid->amount }}
                                        </td>
                                        <td>{{ $bid->provider->name ?? 'N/A' }}</td>
                                        <td>{{ $bid->provider->email ?? 'N/A' }}</td>
                                        <td>{{ $bid->description ?? 'N/A' }}</td>
                                        <td>{{ ucfirst($bid->status) }}</td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">{{ __('static.bid.bids_not_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endisset
@isset($delete)
    @can($delete)
        <a href="#confirmationModal{{ $serviceRequest->id }}" data-bs-toggle="modal" class="delete-svg">
            <i data-feather="trash-2" class="remove-icon delete-confirmation"></i>
        </a>
        <!-- Delete Confirmation -->
        <div class="modal fade" id="confirmationModal{{ $serviceRequest->id }}" tabindex="-1"
            aria-labelledby="confirmationModalLabel{{ $serviceRequest->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-start">
                        <div class="main-img">
                            <i data-feather="trash-2"></i>
                        </div>
                        <div class="text-center">
                            <div class="modal-title"> {{ __('static.delete_message') }}</div>
                            <p>{{ __('static.delete_note') }}</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('backend.serviceRequest.destroy', $serviceRequest->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn cancel" data-bs-dismiss="modal"
                                type="button">{{ __('static.cancel') }}</button>
                            <button class="btn btn-primary delete spinner-btn"
                                type="submit">{{ __('static.delete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endisset

