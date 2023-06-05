<x-app-layout>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    @can('view-page')
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h1>{{ __('ユーザー一覧') }}</h1>
                    <div class="col-lg-12">
                        <form method="GET" action="{{ route('users.index') }}" class="form-inline">
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                   placeholder="名前を入力してください">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="search_option" id="search_name"
                                       value="name" {{ request('search_option') === 'name' ? 'checked' : '' }}>
                                <label class="form-check-label" for="search_name">
                                    ユーザー名
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="search_option" id="search_company"
                                       value="company" {{ request('search_option') === 'company' ? 'checked' : '' }}>
                                <label class="form-check-label" for="search_company">
                                    会社名
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="search_option" id="search_section"
                                       value="section" {{ request('search_option') === 'section' ? 'checked' : '' }}>
                                <label class="form-check-label" for="search_section">
                                    部署名
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">検索</button>
                        </form>
                        @error('search')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('名前') }}</th>
                            <th>{{ __('所属  [会社]') }}</th>
                            <th>{{ __('所属  [部署]') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->company->name }}</td>
                                <td>
                                    @if ($user->sections->isEmpty())
                                        {{ __('未登録') }}
                                    @else
                                        @foreach($user->sections as $section)
                                            {{ $section->name }}
                                        @endforeach
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <form method="POST" action="{{ route('users.csv-export-records.store') }}">
                        @csrf
                        @csrf
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <button class="btn btn-primary">
                            CSVダウンロード
                        </button>
                    </form>

                    <!-- ページネーションのリンク表示 -->
                    {{ $users->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    @else
        <!-- 認可エラーの表示 -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h1>{{ __('このページはユーザー権限での閲覧が制限されています') }}</h1>
                </div>
            </div>
        </div>
    @endcan
</x-app-layout>
