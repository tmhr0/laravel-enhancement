<x-app-layout>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
          integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    @can('view-page')
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Download User</th>
                            <th>File Name</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->user->name }}</td>
                                <td>{{ $record->file_name }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
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
