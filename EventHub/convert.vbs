Set objWord = CreateObject("Word.Application")
objWord.Visible = False
objWord.DisplayAlerts = 0

Dim fso
Set fso = CreateObject("Scripting.FileSystemObject")
Dim docxPath, pdfPath
docxPath = fso.GetAbsolutePathName("storage\app\public\agreements\agreement_35_v2.docx")
pdfPath = fso.GetAbsolutePathName("storage\app\public\agreements\test_vbs.pdf")

On Error Resume Next
Set objDoc = objWord.Documents.Open(docxPath)
If Err.Number <> 0 Then
    WScript.Echo "Error opening document: " & Err.Description
    objWord.Quit
    WScript.Quit 1
End If

objDoc.SaveAs pdfPath, 17

If Err.Number <> 0 Then
    WScript.Echo "Error saving document: " & Err.Description
    objDoc.Close
    objWord.Quit
    WScript.Quit 1
End If

objDoc.Close False
objWord.Quit
WScript.Echo "Success"
