import { commandsCtx, defaultValueCtx, Editor, editorViewCtx, rootCtx } from "@milkdown/core";

import hljs from "highlight.js/lib/core";
import bash from "highlight.js/lib/languages/bash";
import css from "highlight.js/lib/languages/css";
import json from "highlight.js/lib/languages/json";
import javascript from "highlight.js/lib/languages/javascript";
import markdown from "highlight.js/lib/languages/markdown";
import php from "highlight.js/lib/languages/php";
import typescript from "highlight.js/lib/languages/typescript";
import xml from "highlight.js/lib/languages/xml";

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

const LAZY_LANG_LOADERS = {
    python: () => import("highlight.js/lib/languages/python"),
    py: () => import("highlight.js/lib/languages/python"),
    sql: () => import("highlight.js/lib/languages/sql"),
    yaml: () => import("highlight.js/lib/languages/yaml"),
    yml: () => import("highlight.js/lib/languages/yaml"),
    ini: () => import("highlight.js/lib/languages/ini"),
    go: () => import("highlight.js/lib/languages/go"),
    rust: () => import("highlight.js/lib/languages/rust"),
};

const LAZY_PRIMARY = ["python", "sql", "yaml", "ini", "go", "rust"];
let lazyPreloaded = false;

function preloadLazyLanguages() {
    if (lazyPreloaded) return;
    lazyPreloaded = true;
    LAZY_PRIMARY.forEach((name) => {
        const loader = LAZY_LANG_LOADERS[name];
        if (!loader) return;
        loader()
            .then(({ default: lang }) => {
                hljs.registerLanguage(name, lang);
            })
            .catch(() => {});
    });
}

window.hljs = hljs;

const SLASH_ITEMS = [
    {
        id: "h1",
        label: "一级标题",
        hint: "H1",
        keywords: "h1 heading title 标题",
        cmd: "WrapInHeading",
        payload: 1,
        normalize: true,
    },
    {
        id: "h2",
        label: "二级标题",
        hint: "H2",
        keywords: "h2 heading 标题",
        cmd: "WrapInHeading",
        payload: 2,
        normalize: true,
    },
    {
        id: "h3",
        label: "三级标题",
        hint: "H3",
        keywords: "h3 heading 标题",
        cmd: "WrapInHeading",
        payload: 3,
        normalize: true,
    },
    {
        id: "h4",
        label: "四级标题",
        hint: "H4",
        keywords: "h4 heading 标题",
        cmd: "WrapInHeading",
        payload: 4,
        normalize: true,
    },
    {
        id: "bullet-list",
        label: "无序列表",
        hint: "•",
        keywords: "bullet ul list 列表",
        cmd: "WrapInBulletList",
        normalize: true,
    },
    {
        id: "ordered-list",
        label: "有序列表",
        hint: "1.",
        keywords: "ordered ol list 列表 number 数字",
        cmd: "WrapInOrderedList",
        normalize: true,
    },
    {
        id: "task-list",
        label: "任务列表",
        hint: "☐",
        keywords: "task todo checkbox 任务 待办",
        action: "taskList",
        normalize: true,
    },
    {
        id: "quote",
        label: "引用块",
        hint: "❝",
        keywords: "quote blockquote 引用",
        cmd: "WrapInBlockquote",
        normalize: true,
    },
    {
        id: "code",
        label: "代码块",
        hint: "</>",
        keywords: "code block 代码 源码 pre",
        cmd: "CreateCodeBlock",
        normalize: true,
    },
    {
        id: "hr",
        label: "分隔线",
        hint: "—",
        keywords: "hr divider separator 分隔 线",
        cmd: "InsertHr",
        normalize: false,
    },
    {
        id: "text",
        label: "正文段落",
        hint: "P",
        keywords: "paragraph text p 正文 段落",
        cmd: "TurnIntoText",
        normalize: false,
    },
];

const BUBBLE_ITEMS = [
    { id: "ToggleStrong", label: "B", title: "加粗 Ctrl+B", className: "font-bold" },
    { id: "ToggleEmphasis", label: "I", title: "斜体 Ctrl+I", className: "italic" },
    { id: "ToggleStrikeThrough", label: "S", title: "删除线", className: "line-through" },
    { id: "ToggleInlineCode", label: "<>", title: "行内代码", className: "font-mono text-[11px]" },
];

function buildSlashMenu() {
    const root = document.createElement("div");
    root.className = "milkdown-slash-menu";
    root.dataset.show = "false";
    root.contentEditable = "false";

    const list = document.createElement("div");
    list.className = "milkdown-slash-menu__list";

    const itemEls = SLASH_ITEMS.map((item, idx) => {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "milkdown-slash-menu__item";
        btn.dataset.itemId = item.id;
        btn.dataset.index = String(idx);
        btn.innerHTML = `
            <span class="milkdown-slash-menu__hint">${item.hint}</span>
            <span class="milkdown-slash-menu__label">${item.label}</span>
        `;
        list.appendChild(btn);
        return { item, btn };
    });

    root.appendChild(list);

    return { root, itemEls };
}

function buildBubbleMenu() {
    const root = document.createElement("div");
    root.className = "milkdown-bubble-menu";
    root.dataset.show = "false";
    root.contentEditable = "false";

    const itemEls = BUBBLE_ITEMS.map((item) => {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = `milkdown-bubble-menu__item ${item.className ?? ""}`;
        btn.title = item.title;
        btn.textContent = item.label;
        root.appendChild(btn);
        return { item, btn };
    });

    return { root, itemEls };
}

function matchSlashTrigger(text) {
    const m = /\/([\w-]*)$/.exec(text);
    return m ? m[1].toLowerCase() : null;
}

function findSlashTriggerRange(view) {
    const { state } = view;
    const { $from } = state.selection;
    const text = $from.parent.textBetween(0, $from.parentOffset, void 0, "\ufffc");
    const m = /\/[\w-]*$/.exec(text);
    if (!m) return null;
    return { from: $from.start() + m.index, to: $from.pos };
}

function applySlashAction(editor, item) {
    editor.action((ctx) => {
        const view = ctx.get(editorViewCtx);
        const commands = ctx.get(commandsCtx);
        const range = findSlashTriggerRange(view);
        if (range) {
            view.dispatch(view.state.tr.delete(range.from, range.to));
        }
        if (item.normalize) {
            commands.call("TurnIntoText");
        }
        if (item.cmd) {
            commands.call(item.cmd, item.payload);
            return;
        }
        if (item.action === "taskList") {
            commands.call("WrapInBulletList");
            const listItemType = view.state.schema.nodes.list_item;
            const $from = view.state.selection.$from;
            for (let d = $from.depth; d > 0; d--) {
                const node = $from.node(d);
                if (node && node.type === listItemType) {
                    const pos = $from.before(d);
                    const tr = view.state.tr.setNodeMarkup(pos, null, {
                        ...node.attrs,
                        checked: false,
                    });
                    view.dispatch(tr);
                    return;
                }
            }
        }
    });
}

function applyBubbleAction(editor, item) {
    editor.action((ctx) => ctx.get(commandsCtx).call(item.id));
}

function registerMilkdownEditor() {
    if (!window.Alpine) {
        document.addEventListener("alpine:init", registerMilkdownEditor, { once: true });
        return;
    }
    if (window.__milkdownEditorRegistered) {
        return;
    }
    window.__milkdownEditorRegistered = true;

    window.Alpine.data("milkdownEditor", (config = {}) => ({
        markdown: config.initial ?? "",
        name: config.name ?? "body",
        editor: null,
        rootEl: null,
        inputEl: null,
        slashMenu: null,
        bubbleMenu: null,
        slashProvider: null,
        bubbleProvider: null,
        slashEl: null,
        bubbleEl: null,
        async mount(el) {
            this.rootEl = el;
            const wrapper = el.closest("[data-markdown-editor]") || el.parentElement;
            this.inputEl = wrapper?.querySelector('input[type="hidden"]') ?? null;
            if (this.inputEl && this.inputEl.value && !this.markdown) {
                this.markdown = this.inputEl.value;
            }

            this.slashMenu = buildSlashMenu();
            this.bubbleMenu = buildBubbleMenu();

            const slashEl = this.slashMenu.root;
            const bubbleEl = this.bubbleMenu.root;
            slashEl.style.position = "fixed";
            slashEl.style.zIndex = "60";
            bubbleEl.style.position = "fixed";
            bubbleEl.style.zIndex = "60";
            this.slashEl = slashEl;
            this.bubbleEl = bubbleEl;
            wrapper.appendChild(slashEl);
            wrapper.appendChild(bubbleEl);

            try {
                const [
                    { SlashProvider, slashFactory },
                    { TooltipProvider, tooltipFactory },
                    { listener, listenerCtx },
                    { commonmark },
                    { gfm },
                    { history },
                    { clipboard },
                    { block },
                ] = await Promise.all([
                    import("@milkdown/plugin-slash"),
                    import("@milkdown/plugin-tooltip"),
                    import("@milkdown/plugin-listener"),
                    import("@milkdown/preset-commonmark"),
                    import("@milkdown/preset-gfm"),
                    import("@milkdown/plugin-history"),
                    import("@milkdown/plugin-clipboard"),
                    import("@milkdown/plugin-block"),
                ]);

                const slashProvider = new SlashProvider({
                    content: slashEl,
                    trigger: "/",
                    shouldShow: (view) => {
                        const text = slashProvider.getContent(view) ?? "";
                        return /\/[\w-]*$/.test(text);
                    },
                });
                const [slashSpec, slashPlugin] = slashFactory("MILKDOWN_SLASH");

                const bubbleProvider = new TooltipProvider({
                    content: bubbleEl,
                    shouldShow: (view) => {
                        if (view.state.selection.empty) return false;
                        const text = view.state.doc.textBetween(
                            view.state.selection.from,
                            view.state.selection.to,
                        );
                        return text.trim().length > 0;
                    },
                });
                const [bubbleSpec, bubblePlugin] = tooltipFactory("MILKDOWN_BUBBLE");

                this.slashProvider = slashProvider;
                this.bubbleProvider = bubbleProvider;

                this.editor = await Editor.make()
                    .config((ctx) => {
                        ctx.set(rootCtx, el);
                        ctx.set(defaultValueCtx, this.markdown);

                        ctx.inject(slashSpec.key, {
                            view: () => ({
                                update: (view, prevState) => slashProvider.update(view, prevState),
                                destroy: () => slashProvider.destroy(),
                            }),
                        });
                        ctx.inject(bubbleSpec.key, {
                            view: () => ({
                                update: (view, prevState) => bubbleProvider.update(view, prevState),
                                destroy: () => bubbleProvider.destroy(),
                            }),
                        });

                        const listenerMgr = ctx.get(listenerCtx);
                        listenerMgr.markdownUpdated((_ctx, md) => {
                            this.markdown = md;
                            if (this.inputEl) {
                                this.inputEl.value = md;
                                this.inputEl.dispatchEvent(new Event("input", { bubbles: true }));
                            }
                        });
                    })
                    .use(commonmark)
                    .use(gfm)
                    .use(history)
                    .use(clipboard)
                    .use(listener)
                    .use(block)
                    .use(slashPlugin)
                    .use(bubblePlugin)
                    .create();

                preloadLazyLanguages();
                this.wireMenuEvents();
            } catch (err) {
                console.error("[milkdown] failed to initialize editor", err);
                el.innerHTML =
                    '<div class="p-3 text-sm text-red-600">编辑器加载失败：' +
                    ((err && (err.message || err.stack)) || String(err))
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;") +
                    "</div>";
            }
        },

        wireMenuEvents() {
            const updateSlashFilter = () => {
                if (!this.editor) return;
                const text = this.editor.action((ctx) => {
                    const view = ctx.get(editorViewCtx);
                    return this.slashProvider.getContent(view) ?? "";
                });
                const query = matchSlashTrigger(text);
                let firstVisible = true;
                let visible = 0;
                this.slashMenu.itemEls.forEach(({ item, btn }) => {
                    const match =
                        !query ||
                        item.label.toLowerCase().includes(query) ||
                        item.keywords.toLowerCase().includes(query);
                    btn.style.display = match ? "" : "none";
                    btn.dataset.active = match && firstVisible ? "true" : "false";
                    if (match) {
                        firstVisible = false;
                        visible++;
                    }
                });
                this.slashEl.dataset.empty = visible === 0 ? "true" : "false";
            };

            this.bubbleMenu.itemEls.forEach(({ item, btn }) => {
                btn.addEventListener("mousedown", (e) => {
                    e.preventDefault();
                    applyBubbleAction(this.editor, item);
                });
            });

            this.slashMenu.itemEls.forEach(({ item, btn }) => {
                btn.addEventListener("mousedown", (e) => {
                    e.preventDefault();
                    applySlashAction(this.editor, item);
                });
            });

            this.slashProvider.onShow = () => {
                this.slashEl.dataset.show = "true";
                updateSlashFilter();
            };
            this.slashProvider.onHide = () => {
                this.slashEl.dataset.show = "false";
            };
            this.bubbleProvider.onShow = () => {
                this.bubbleEl.dataset.show = "true";
            };
            this.bubbleProvider.onHide = () => {
                this.bubbleEl.dataset.show = "false";
            };

            this.rootEl.addEventListener("keydown", (e) => {
                if (this.slashEl.dataset.show !== "true") return;
                const visible = this.slashMenu.itemEls.filter(
                    ({ btn }) => btn.style.display !== "none",
                );
                if (visible.length === 0) return;
                const currentIdx = visible.findIndex(({ btn }) => btn.dataset.active === "true");
                if (e.key === "ArrowDown") {
                    e.preventDefault();
                    visible.forEach(({ btn }) => (btn.dataset.active = "false"));
                    const next = visible[(currentIdx + 1 + visible.length) % visible.length];
                    next.btn.dataset.active = "true";
                    next.btn.scrollIntoView({ block: "nearest" });
                } else if (e.key === "ArrowUp") {
                    e.preventDefault();
                    visible.forEach(({ btn }) => (btn.dataset.active = "false"));
                    const prev = visible[(currentIdx - 1 + visible.length) % visible.length];
                    prev.btn.dataset.active = "true";
                    prev.btn.scrollIntoView({ block: "nearest" });
                } else if (e.key === "Enter") {
                    e.preventDefault();
                    const active = visible.find(({ btn }) => btn.dataset.active === "true");
                    if (active) {
                        active.btn.dispatchEvent(
                            new MouseEvent("mousedown", { bubbles: true, cancelable: true }),
                        );
                    }
                }
            });

            const view = this.editor.action((ctx) => ctx.get(editorViewCtx));
            view.dom.addEventListener("input", updateSlashFilter);
        },

        destroy() {
            try {
                this.bubbleProvider?.destroy();
                this.slashProvider?.destroy();
            } catch (e) {
                console.error(e);
            }
            if (this.slashMenu?.root?.parentElement) {
                this.slashMenu.root.parentElement.removeChild(this.slashMenu.root);
            }
            if (this.bubbleMenu?.root?.parentElement) {
                this.bubbleMenu.root.parentElement.removeChild(this.bubbleMenu.root);
            }
            if (this.editor) {
                try {
                    this.editor.destroy();
                } catch (e) {
                    console.error(e);
                }
            }
        },
    }));
}

registerMilkdownEditor();
