package bundler

import (
	"fmt"
	"io/ioutil"
	"os"
	"path/filepath"
	"../../dependency"
	"../../cache"
)

func dependencies(file dependency.FileContent) []dependency.File {
	return dependency.Js(file, []string {".js"})
}

func dependenciesCached(file dependency.FileContent, c *cache.Cache) []dependency.File {
	filestat, _ := os.Stat(file.Meta.File)
	fmktime := filestat.ModTime().Unix()

	if hit, deps := cache.Hit(c, file.Meta, fmktime); hit {
		return deps
	}

	return cache.Put(c, file.Meta, dependencies(file), fmktime)
}

func bundle(input_files []string) {
	c := cache.Load()

	// Add input element to input
	for _, f := range input_files {
		path := filepath.Clean(f)
		buf, _ := ioutil.ReadFile(path)

		file := dependency.FileContent{Meta: dependency.File{Name: f, File: path}, Content: buf}
		deps := dependenciesCached(file, c)

		js := "System.registerDynamic(["

		// dependencies

		for i, dep := range deps {
			if i > 0 {
				js += ", "
			}

			js += "'"
			js += dep.Name
			js += "'"
		}

		js += "], true, function(require, exports, module) {\n"

		// module
		js += string(file.Content)

		js += "\n});"

		fmt.Println(js)
	}
}

func Init(files []string) {
	bundle(files)
}