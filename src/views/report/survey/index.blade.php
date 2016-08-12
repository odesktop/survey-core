@section('content')
  @include('partial.header')

  <hr>

  <h2>Surveys</h2>

  <table class="table">
    <tr>
      <td>
        <a href="{{ route('survey-core::task.overall.summary', [$job->manaba_jobid]) }}" onclick="return confirm('タスクを作成します。よろしいですか？')">
          全体の集計を実行
        </a>
      </td>
      <td>
        <a href="{{ route('survey-core::summary.overall.show', [$job->manaba_jobid]) }}">
          個票表示
        </a>
      </td>
      <td>
        {{ link_to_route('summary.total.clearall', '全体の集計をクリア', ['job' => $job->id], ['onclick' => 'return confirm("クリアします。よろしいですか？")']) }}
      </td>
    </tr>
    <tr>
      <td>
        {{ link_to_route('pdf.total.makeall', '全体のPDFを生成', ['job' => $job->id], ['onclick' => 'return confirm("タスクを作成します。よろしいですか？")']) }}
      </td>
      <td>
        全体のPDF
      </td>
      <td>
        {{ link_to_route('pdf.all.deleteall', '全体のPDFをを削除', ['job' => $job->id], ['onclick' => 'return confirm("削除します。よろしいですか？")']) }}
      </td>
    </tr>
    <tr>
      <td>
        {{ link_to_route('zip.all.make', '全体のZIPファイルを生成', ['job' => $job->id], ['onclick' => 'return confirm("タスクを作成します。よろしいですか？")']) }}
      </td>
      <td>
        @if ($job->hasZipFile('all'))
          {{ link_to($job->zipUrl('all'), '全体のZIPファイル') }}
        @else
          <span class="text-disabled">全体のZIPファイル</span>
        @endif
      </td>
      <td>
        @if ($job->hasZipFile('all'))
          {{ link_to_route('zip.all.delete', '全体のZIPファイルを削除', ['job' => $job->id], ['onclick' => 'return confirm("削除します。よろしいですか？")']) }}
        @else
          <span class="text-disabled">科目区分別ZIPファイルを削除</span>
        @endif
      </td>
    </tr>
    <tr>
      <td>
        {{ link_to_route('zip.org.make', '科目区分別ZIPファイルを生成', ['job' => $job->id], ['onclick' => 'return confirm("タスクを作成します。よろしいですか？")']) }}
      </td>
      <td>
        @if ($job->hasZipFile('org'))
          {{ link_to($job->zipUrl('org'), '科目区分別ZIPファイル') }}
        @else
          <span class="text-disabled">科目区分別ZIPファイル</span>
        @endif
      </td>
      <td>
        @if ($job->hasZipFile('org'))
          {{ link_to_route('zip.org.delete', '科目別まとめてZIPファイルを削除', ['job' => $job->id], ['onclick' => 'return confirm("削除します。よろしいですか？")']) }}
        @else
          <span class="text-disabled">科目区分別ZIPファイルを削除</span>
        @endif
      </td>
    </tr>
    <tr>
      <td>
        {{ link_to_route('zip.course.make', '科目別ZIPファイルを生成', ['job' => $job->id], ['onclick' => 'return confirm("タスクを作成します。よろしいですか？")']) }}
      </td>
      <td>
        @if (File::exists($job->zipFilePath('course')))
          {{ link_to($job->zipUrl('course'), '科目別ZIPファイル') }}
        @else
          <span class="text-disabled">科目別ZIPファイル</span>
        @endif
      </td>
      <td>
        @if (File::exists($job->zipFilePath('course')))
          {{ link_to_route('zip.course.delete', '科目別ZIPファイルを削除', ['job' => $job->id], ['onclick' => 'return confirm("削除します。よろしいですか？")']) }}
        @else
          <span class="text-disabled">科目別ZIPファイルを削除</span>
        @endif
      </td>
    </tr>
    <tr>
      <td>
        {{ link_to_route('zip.courseorg.make', '科目別まとめてZIPファイルを生成', ['job' => $job->id], ['onclick' => 'return confirm("タスクを作成します。よろしいですか？")']) }}
      </td>
      <td>
        @if ($job->hasZipFile('courseorg'))
          {{ link_to($job->zipUrl('courseorg'), '科目別まとめてZIPファイル') }}
        @else
          <span class="text-disabled">科目別まとめてZIPファイル</span>
        @endif
      </td>
      <td>
        @if ($job->hasZipFile('courseorg'))
          {{ link_to_route('zip.courseorg.delete', '科目別まとめてZIPファイルを削除', ['job' => $job->id], ['onclick' => 'return confirm("削除します。よろしいですか？")']) }}
        @else
          <span class="text-disabled">科目別まとめてZIPファイルを削除</span>
        @endif
      </td>
    </tr>
  </table>

  <p></p>

  {{ $viewModel->links() }}

  <table class="table">
    {{ $viewModel->tableHeader() }}
    @foreach ($viewModel->tableRows() as $row)
      {{ $row }}
    @endforeach
  </table>

  {{ $viewModel->links() }}

@stop