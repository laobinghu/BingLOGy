import hljs from "highlight.js/lib/core";
import javascript from "highlight.js/lib/languages/javascript";
import typescript from "highlight.js/lib/languages/typescript";
import php from "highlight.js/lib/languages/php";
import bash from "highlight.js/lib/languages/bash";
import json from "highlight.js/lib/languages/json";
import xml from "highlight.js/lib/languages/xml";
import css from "highlight.js/lib/languages/css";
import markdown from "highlight.js/lib/languages/markdown";
import python from "highlight.js/lib/languages/python";
import sql from "highlight.js/lib/languages/sql";
import yaml from "highlight.js/lib/languages/yaml";
import ini from "highlight.js/lib/languages/ini";
import go from "highlight.js/lib/languages/go";
import rust from "highlight.js/lib/languages/rust";

hljs.registerLanguage("javascript", javascript);
hljs.registerLanguage("js", javascript);
hljs.registerLanguage("typescript", typescript);
hljs.registerLanguage("ts", typescript);
hljs.registerLanguage("php", php);
hljs.registerLanguage("bash", bash);
hljs.registerLanguage("sh", bash);
hljs.registerLanguage("json", json);
hljs.registerLanguage("xml", xml);
hljs.registerLanguage("html", xml);
hljs.registerLanguage("css", css);
hljs.registerLanguage("markdown", markdown);
hljs.registerLanguage("md", markdown);
hljs.registerLanguage("python", python);
hljs.registerLanguage("py", python);
hljs.registerLanguage("sql", sql);
hljs.registerLanguage("yaml", yaml);
hljs.registerLanguage("yml", yaml);
hljs.registerLanguage("ini", ini);
hljs.registerLanguage("go", go);
hljs.registerLanguage("rust", rust);

function highlightAll() {
    document.querySelectorAll("pre code").forEach((block) => {
        if (block.dataset.highlighted === "yes") return;
        hljs.highlightElement(block);
        block.dataset.highlighted = "yes";
    });
}

function enhanceCodeBlocks() {
    document.querySelectorAll("pre").forEach((pre) => {
        if (pre.dataset.enhanced === "yes") return;
        pre.dataset.enhanced = "yes";

        pre.classList.add("relative", "group");

        const button = document.createElement("button");
        button.type = "button";
        button.textContent = "Copy";
        button.className =
            "copy-code-btn absolute right-2 top-2 rounded border border-stone-300 bg-white/80 px-2 py-0.5 text-xs font-medium text-stone-600 opacity-0 transition group-hover:opacity-100 dark:border-stone-600 dark:bg-stone-800/80 dark:text-stone-300";
        button.addEventListener("click", async () => {
            const code = pre.querySelector("code");
            if (!code) return;
            try {
                await navigator.clipboard.writeText(code.innerText);
                button.textContent = "Copied";
                setTimeout(() => (button.textContent = "Copy"), 1500);
            } catch (e) {
                console.error(e);
            }
        });
        pre.appendChild(button);
    });
}

function init() {
    highlightAll();
    enhanceCodeBlocks();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
} else {
    init();
}

document.addEventListener("livewire:navigated", init);
