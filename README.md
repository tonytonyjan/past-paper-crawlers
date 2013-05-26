[考古題世界]: http://past-paper.com
[文件]: http://goo.gl/FUBMO
[SFTP]: http://goo.gl/UZee5
[大兜]: mailto:tonytonyjan@gmail.com

## 考古題爬蟲黑克松
本專案是為了[考古題世界]而生，詳情請查閱此[文件]。

## 注意事項

### 規範

*   本 repository 只接受程式碼。
*   爬完的結果請放到 [SFTP]（權限請跟[大兜]索取）。

### 檔案結構

一個學校一個資料夾，以英文縮寫命名。內含一個 [past_papers.json](https://gist.github.com/tonytonyjan/4c308f311f59439cc826) 檔案。

    NCTU
    ├── files
    │   ├── cnlz1801.pdf
    │   ├── cnlz1803.pdf
    │   ├── ……
    │   ├── ece1201.pdf
    │   └── ece1202.pdf
    └── past_papers.json

#### past_papers.json

past_papers.json 為一個 array，內含多個 object：

    {
      "school": "國立交通大學",
      "department": "電機工程學系",
      "program": null,
      "subject": "線性代數與機率",
      "year": 2012,
      "exam_type": ”入學考”, // 5/25 新增
      "file_paths": [
        "files/eed1211.pdf"
      ]
    }

*   `program`：OOXX 組（例：資訊工程學系網路與多媒體組，其中「網路與多媒體組」為 `program`）
*   `file_paths`：為相對路徑。
*   `exam_type`：(入學考 | 轉學考)，這次爬蟲應該只有入學、轉學兩者，如果有發現其他的種類，請在[文件]做討論。

## 提示

### 取出系所和組別的表達式

`/(.*[班系所])(.*組)/`
例如：資訊工程學系網路與多媒體組，會被拆成「資訊工程學系」和「網路與多媒體組」
