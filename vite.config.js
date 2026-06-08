import { defineConfig } from "vite-plus";
import laravel from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import tailwindcss from "@tailwindcss/vite";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    staged: {
        "*": "vp check --fix",
    },
    fmt: {},
    lint: {
        jsPlugins: [{ name: "vite-plus", specifier: "vite-plus/oxlint-plugin" }],
        rules: { "vite-plus/prefer-vite-plus-imports": "error" },
        options: { typeAware: true, typeCheck: true },
    },
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js", "resources/js/editor.js"],
            refresh: true,
            fonts: [
                bunny("Instrument Sans", {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            "lodash-es": path.resolve(__dirname, "resources/js/shims/lodash-es.js"),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes("node_modules")) return undefined;
                    if (/highlight\.js\/(es|lib)\/languages\//.test(id)) return "highlight-lazy";
                    if (id.includes("@milkdown")) return "milkdown-vendor";
                    if (id.includes("@floating-ui")) return "floating-ui-vendor";
                    if (id.includes("highlight.js")) return "highlight-vendor";
                    return "vendor";
                },
            },
        },
    },
    server: {
        cors: true,
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
