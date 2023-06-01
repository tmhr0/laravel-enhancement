<x-app-layout>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    @can('view-page')
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <h1>{{ __('ユーザー一覧') }}</h1>
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

                        <!-- ページネーションのリンク表示 -->
                        {{ $users->links() }}
                    </div>
                </div>
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                    </div>
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
