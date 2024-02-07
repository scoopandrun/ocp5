// Hide the textarea element
/** @type {HTMLTextAreaElement} */
const bodyTextarea = document.getElementById("body");
if (bodyTextarea) {
  bodyTextarea.setAttribute("hidden", true);
}

const quillEditor = new Quill("#quill-editor", {
  modules: {
    toolbar: [
      ["bold", "italic", "underline", "strike"], // toggled buttons
      ["blockquote", "code-block"],

      [{ list: "ordered" }, { list: "bullet" }],
      [{ script: "sub" }, { script: "super" }], // superscript/subscript
      [{ indent: "-1" }, { indent: "+1" }], // outdent/indent
      [{ direction: "rtl" }], // text direction

      [{ size: ["small", false, "large", "huge"] }], // custom dropdown
      [{ header: [1, 2, 3, 4, 5, 6, false] }],

      [{ color: [] }, { background: [] }], // dropdown with defaults from theme
      [{ font: [] }],
      [{ align: [] }],

      ["clean"], // remove formatting button
    ],
  },
  placeholder: "Corps du post...",
  theme: "snow",
});

try {
  quillEditor.setContents(JSON.parse(bodyTextarea.textContent));
} catch (error) {
  quillEditor.setText(bodyTextarea.textContent);
}

/** @type {HTMLFormElement} */
const postForm = document.getElementById("postForm");

if (postForm) {
  postForm.addEventListener("submit", (e) => {
    bodyTextarea.textContent = JSON.stringify(quillEditor.getContents());
  });
}
