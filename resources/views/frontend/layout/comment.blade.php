@use('app\Helpers\Helpers')
<li class="review-card">
    <div class="review-detail">
        <div class="review-top-box">
            <div class="review-image-box">
                @php
                $profileImg = $comment->user->media?->first()?->getUrl();
                @endphp
                @if(Helpers::isFileExistsFromURL($profileImg))
                <img src="{{ Helpers::isFileExistsFromURL($profileImg, true) }}" alt="feature" class="img-fluid">
                @else
                <span class="profile-name initial-letter">{{ substr($comment?->user?->name, 0, 1) }}</span>
                @endif
            </div>
            <div class="review-auth-name">
                <h4>
                    @if($comment->user_id == $blog->created_by_id)
                    {{ __('frontend::static.comment.owner') }}
                    @else
                    {{ $comment->user->name }}
                    @endif
                </h4>
                <span>({{ __('frontend::static.comment.commented') }}
                    {{ $comment->created_at->diffForHumans() }})</span>
            </div>
        </div>
        <div class="review-right-box">
            <div class="review-comment-box">
                <div class="review">
                    <p>{{ $comment->message }}</p>
                    @if(Auth::check() && Auth::id() != $comment->user_id)
                    <form action="{{ route('frontend.comments.store', $blog->id) }}"
                        class="comment-box reply-box d-flex align-items-center gap-3" method="POST">
                        @csrf
                        <textarea class="form-control form-control-white" maxlength="150" placeholder="Reply"
                            name="message" rows="1"></textarea>
                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                        <button type="submit"
                            class="btn btn-solid reply-btn">{{ __('frontend::static.comment.reply') }}</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if($comment->replies->count())
    <ul>
        @foreach($comment->replies as $reply)
        @include('frontend.layout.comment', ['comment' => $reply])
        @endforeach
    </ul>
    @endif
</li>