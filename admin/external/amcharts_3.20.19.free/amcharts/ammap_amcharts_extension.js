(function () {
    var e = window.AmCharts;
    e.AmMap = e.Class({inherits: e.AmChart, construct: function (a) {
        this.cname = "AmMap";
        this.type = "map";
        this.theme = a;
        this.svgNotSupported = "This browser doesn't support SVG. Use Chrome, Firefox, Internet Explorer 9 or later.";
        this.createEvents("rollOverMapObject", "rollOutMapObject", "clickMapObject", "mouseDownMapObject", "selectedObjectChanged", "homeButtonClicked", "zoomCompleted", "dragCompleted", "positionChanged", "writeDevInfo", "click", "descriptionClosed");
        this.zoomDuration = .6;
        this.zoomControl = new e.ZoomControl(a);
        this.fitMapToContainer = !0;
        this.mouseWheelZoomEnabled = this.backgroundZoomsToTop = !1;
        this.allowClickOnSelectedObject = this.useHandCursorOnClickableOjects = this.showBalloonOnSelectedObject = !0;
        this.showObjectsAfterZoom = this.wheelBusy = !1;
        this.zoomOnDoubleClick = this.useObjectColorForBalloon = !0;
        this.allowMultipleDescriptionWindows = !1;
        this.dragMap = this.centerMap = this.linesAboveImages = !0;
        this.colorSteps = 5;
        this.forceNormalize = !1;
        this.showAreasInList = !0;
        this.showLinesInList =
            this.showImagesInList = !1;
        this.areasProcessor = new e.AreasProcessor(this);
        this.areasSettings = new e.AreasSettings(a);
        this.imagesProcessor = new e.ImagesProcessor(this);
        this.imagesSettings = new e.ImagesSettings(a);
        this.linesProcessor = new e.LinesProcessor(this);
        this.linesSettings = new e.LinesSettings(a);
        this.initialTouchZoom = 1;
        this.showDescriptionOnHover = !1;
        e.AmMap.base.construct.call(this, a);
        this.creditsPosition = "bottom-left";
        this.product = "ammap";
        this.areasClasses = {};
        this.updatableImages = [];
        e.applyTheme(this,
            a, this.cname)
    }, initChart: function () {
        this.zoomInstantly = !0;
        var a = this.container;
        this.panRequired = !0;
        if (this.sizeChanged && e.hasSVG && this.chartCreated) {
            this.updatableImages = [];
            this.freeLabelsSet && this.freeLabelsSet.remove();
            this.freeLabelsSet = a.set();
            this.container.setSize(this.realWidth, this.realHeight);
            this.resizeMap();
            this.drawBackground();
            this.redrawLabels();
            this.drawTitles();
            this.processObjects(!0);
            this.rescaleObjects();
            this.zoomControl.init(this, a);
            this.drawBg();
            var b = this.smallMap;
            b && b.init(this,
                a);
            (b = this.valueLegend) && b.init(this, a);
            this.sizeChanged = !1;
            this.zoomToLongLat(this.zLevelTemp, this.zLongTemp, this.zLatTemp, !0);
            this.previousWidth = this.realWidth;
            this.previousHeight = this.realHeight;
            this.updateSmallMap();
            this.linkSet.toFront();
            this.zoomControl.update && this.zoomControl.update()
        } else(e.AmMap.base.initChart.call(this), e.hasSVG) ? (this.dataChanged && (this.parseData(), this.dispatchDataUpdated = !0, this.dataChanged = !1, a = this.legend) && (a.position = "absolute", a.invalidateSize()), this.createDescriptionsDiv(),
            this.svgAreas = [], this.svgAreasById = {}, this.drawChart()) : (this.chartDiv.style.textAlign = "", this.chartDiv.setAttribute("class", "ammapAlert"), this.chartDiv.innerHTML = this.svgNotSupported, this.fire({type: "failed", chart: this}))
    }, storeTemp: function () {
        if (e.hasSVG && 0 < this.realWidth && 0 < this.realHeight) {
            var a = this.mapContainer.getBBox();
            0 < a.width && 0 < a.height && (a = this.zoomLongitude(), isNaN(a) || (this.zLongTemp = a), a = this.zoomLatitude(), isNaN(a) || (this.zLatTemp = a), a = this.zoomLevel(), isNaN(a) || (this.zLevelTemp =
                a))
        }
    }, invalidateSize: function () {
        this.storeTemp();
        e.AmMap.base.invalidateSize.call(this)
    }, validateSize: function () {
        this.storeTemp();
        e.AmMap.base.validateSize.call(this)
    }, handleWheelReal: function (a) {
        if (!this.wheelBusy) {
            this.stopAnimation();
            var b = this.zoomLevel(), c = this.zoomControl, d = c.zoomFactor;
            this.wheelBusy = !0;
            a = e.fitToBounds(0 < a ? b * d : b / d, c.minZoomLevel, c.maxZoomLevel);
            d = this.mouseX / this.mapWidth;
            c = this.mouseY / this.mapHeight;
            d = (this.zoomX() - d) * (a / b) + d;
            b = (this.zoomY() - c) * (a / b) + c;
            this.zoomTo(a, d, b)
        }
    },
        addLegend: function (a, b) {
            a.position = "absolute";
            a.autoMargins = !1;
            a.valueWidth = 0;
            a.switchable = !1;
            e.AmMap.base.addLegend.call(this, a, b);
            void 0 === a.enabled && (a.enabled = !0);
            return a
        }, handleLegendEvent: function () {
        }, createDescriptionsDiv: function () {
            if (!this.descriptionsDiv) {
                var a = document.createElement("div"), b = a.style;
                b.position = "absolute";
                b.left = "0px";
                b.top = "0px";
                this.descriptionsDiv = a
            }
            this.containerDiv.appendChild(this.descriptionsDiv)
        }, drawChart: function () {
            e.AmMap.base.drawChart.call(this);
            var a = this.dataProvider;
            this.dataProvider = a = e.extend(a, new e.MapData, !0);
            this.areasSettings = e.processObject(this.areasSettings, e.AreasSettings, this.theme);
            this.imagesSettings = e.processObject(this.imagesSettings, e.ImagesSettings, this.theme);
            this.linesSettings = e.processObject(this.linesSettings, e.LinesSettings, this.theme);
            var b = this.container;
            this.mapContainer && this.mapContainer.remove();
            this.mapContainer = b.set();
            this.graphsSet.push(this.mapContainer);
            var c;
            a.map && (c = e.maps[a.map]);
            a.mapVar && (c = a.mapVar);
            c ? (this.svgData = c.svg,
                this.getBounds(), this.buildEverything()) : (a = a.mapURL) && this.loadXml(a);
            this.balloonsSet.toFront()
        }, drawBg: function () {
            var a = this;
            a.background.click(function () {
                a.handleBackgroundClick()
            });
            a.background.mouseover(function () {
                a.rollOutMapObject(a.previouslyHovered)
            })
        }, buildEverything: function () {
            if (0 < this.realWidth && 0 < this.realHeight) {
                var a = this.container, b = this.dataProvider;
                this.projection || (this.projection = b.projection, this.projection || (this.projection = "equirectangular"));
                this.updatableImages = [];
                var c =
                    this.projection;
                c && (this.projectionFunction = e[c]);
                this.projectionFunction || (this.projectionFunction = e.equirectangular);
                this.dpProjectionFunction = e[b.projection];
                this.dpProjectionFunction || (this.dpProjectionFunction = e.equirectangular);
                this.zoomControl = e.processObject(this.zoomControl, e.ZoomControl, this.theme);
                this.zoomControl.init(this, a);
                this.drawBg();
                this.buildSVGMap();
                this.projectionFunction && c != b.projection || this.forceNormalize ? (this.normalizeMap(), this.changeProjection()) : this.fixMapPosition();
                if (c = this.smallMap)c = e.processObject(c, e.SmallMap, this.theme), c.init(this, a), this.smallMap = c;
                isNaN(b.zoomX) && isNaN(b.zoomY) && isNaN(b.zoomLatitude) && isNaN(b.zoomLongitude) && (this.centerMap ? (c = this.xyToCoordinates(this.mapWidth / 2, this.mapHeight / 2), b.zoomLongitudeC = c.longitude, b.zoomLatitudeC = c.latitude) : (b.zoomX = 0, b.zoomY = 0), this.zoomInstantly = !0);
                this.selectObject(this.dataProvider);
                this.processAreas();
                if (b = this.valueLegend)this.valueLegend = b = e.processObject(b, e.ValueLegend, this.theme), b.init(this,
                    a);
                this.objectList && (a = this.objectList = e.processObject(this.objectList, e.ObjectList)) && (this.clearObjectList(), a.init(this));
                this.dispDUpd();
                this.updateSmallMap();
                this.linkSet.toFront()
            } else this.cleanChart()
        }, hideGroup: function (a) {
            this.showHideGroup(a, !1)
        }, showGroup: function (a) {
            this.showHideGroup(a, !0)
        }, showHideGroup: function (a, b) {
            this.showHideReal(this.imagesProcessor.allObjects, a, b);
            this.showHideReal(this.areasProcessor.allObjects, a, b);
            this.showHideReal(this.linesProcessor.allObjects, a, b)
        }, showHideReal: function (a, b, c) {
            var d;
            for (d = 0; d < a.length; d++) {
                var f = a[d];
                if (f.groupId == b) {
                    var e = f.displayObject;
                    e && (c ? (f.hidden = !1, e.show()) : (f.hidden = !0, e.hide()))
                }
            }
        }, makeObjectAccessible: function (a) {
            if (a.accessibleLabel) {
                var b = this.formatString(a.accessibleLabel, a);
                a.displayObject && this.makeAccessible(a.displayObject, b, "menuitem")
            }
        }, update: function () {
            if (e.hasSVG) {
                e.AmMap.base.update.call(this);
                this.zoomControl && this.zoomControl.update && this.zoomControl.update();
                for (var a = 0, b = this.updatableImages.length; a < b; a++)this.updatableImages[a].update()
            }
        },
        animateMap: function () {
            var a = this;
            a.totalFrames = a.zoomDuration * e.updateRate;
            a.totalFrames += 1;
            a.frame = 0;
            a.tweenPercent = 0;
            a.balloon.hide(0);
            setTimeout(function () {
                a.updateSize.call(a)
            }, 1E3 / e.updateRate)
        }, updateSize: function () {
            var a = this, b = a.totalFrames;
            a.preventHover = !0;
            a.frame <= b ? (a.frame++, b = e.easeOutSine(0, a.frame, 0, 1, b), 1 <= b ? (b = 1, a.preventHover = !1, a.wheelBusy = !1) : window.requestAnimationFrame ? window.requestAnimationFrame(function () {
                a.updateSize.call(a)
            }) : setTimeout(function () {
                    a.updateSize.call(a)
                },
                1E3 / e.updateRate), .8 < b && (a.preventHover = !1)) : (b = 1, a.preventHover = !1, a.wheelBusy = !1);
            a.tweenPercent = b;
            a.rescaleMapAndObjects()
        }, rescaleMapAndObjects: function () {
            var a = this.initialScale, b = this.initialX, c = this.initialY, d = this.tweenPercent, a = a + (this.finalScale - a) * d;
            this.mapContainer.translate(b + (this.finalX - b) * d, c + (this.finalY - c) * d, a, !0);
            if (this.areasSettings.adjustOutlineThickness) {
                for (var b = this.svgAreas, f = 0; f < b.length; f++)(c = b[f]) && c.setAttr("stroke-width", this.areasSettings.outlineThickness / a / this.mapScale);
                if (b = this.dataProvider.areas)for (f = 0; f < b.length; f++) {
                    var c = b[f], e = c.displayObject;
                    e && e.setAttr("stroke-width", c.outlineThicknessReal / a / this.mapScale)
                }
            }
            this.rescaleObjects();
            this.positionChanged();
            this.updateSmallMap();
            1 == d && this.fire({type: "zoomCompleted", chart: this})
        }, updateSmallMap: function () {
            this.smallMap && this.smallMap.update()
        }, rescaleObjects: function () {
            var a = this.mapContainer.scale, b = this.imagesProcessor.objectsToResize, c;
            for (c = 0; c < b.length; c++) {
                var d = b[c].image, f = b[c].scale, e = b[c].mapImage;
                isNaN(e.selectedScaleReal) || e != this.selectedObject || (e.tempScale = f, f *= e.selectedScaleReal);
                d.translate(d.x, d.y, f / a, !0)
            }
            b = this.imagesProcessor.labelsToReposition;
            for (c = 0; c < b.length; c++)d = b[c], d.imageLabel && this.imagesProcessor.positionLabel(d.imageLabel, d, d.labelPositionReal);
            b = this.linesProcessor;
            if (d = b.linesToResize)for (c = 0; c < d.length; c++)f = d[c], f.line.setAttr("stroke-width", f.thickness / a);
            b = b.objectsToResize;
            for (c = 0; c < b.length; c++)d = b[c], d.translate(d.x, d.y, 1 / a, !0)
        }, handleTouchEnd: function (a) {
            this.initialDistance =
                NaN;
            this.mouseIsDown = this.isDragging = !1;
            e.AmMap.base.handleTouchEnd.call(this, a)
        }, handleMouseDown: function (a) {
            e.resetMouseOver();
            this.mouseIsDown = this.mouseIsOver = !0;
            this.balloon.hide(0);
            a && this.mouseIsOver && a.preventDefault && this.panEventsEnabled && a.preventDefault();
            if (this.chartCreated && !this.preventHover && (this.initialTouchZoom = this.zoomLevel(), this.dragMap && (this.stopAnimation(), this.mapContainerClickX = this.mapContainer.x, this.mapContainerClickY = this.mapContainer.y), a || (a = window.event), a.shiftKey &&
                !0 === this.developerMode && this.getDevInfo(), a && a.touches)) {
                var b = this.mouseX, c = this.mouseY, d = a.touches.item(1);
                d && this.panEventsEnabled && this.boundingRect && (a = d.clientX - this.boundingRect.left, d = d.clientY - this.boundingRect.top, this.middleXP = (b + (a - b) / 2) / this.realWidth, this.middleYP = (c + (d - c) / 2) / this.realHeight, this.initialDistance = Math.sqrt(Math.pow(a - b, 2) + Math.pow(d - c, 2)))
            }
        }, stopDrag: function () {
            this.isDragging = !1
        }, handleReleaseOutside: function () {
            if (e.isModern) {
                var a = this;
                e.AmMap.base.handleReleaseOutside.call(a);
                a.mouseIsDown = !1;
                setTimeout(function () {
                    a.resetPinch.call(a)
                }, 100);
                if (!a.preventHover) {
                    a.stopDrag();
                    var b = a.zoomControl;
                    b && b.draggerUp && b.draggerUp();
                    a.mapWasDragged = !1;
                    var b = a.mapContainer, c = a.mapContainerClickX, d = a.mapContainerClickY;
                    isNaN(c) || isNaN(d) || !(3 < Math.abs(b.x - c) || 3 < Math.abs(b.y - d)) || (a.mapWasDragged = !0, b = {type: "dragCompleted", zoomX: a.zoomX(), zoomY: a.zoomY(), zoomLevel: a.zoomLevel(), chart: a}, a.fire(b));
                    (a.mouseIsOver && !a.mapWasDragged && !a.skipClick || a.wasTouched && 3 > Math.abs(a.mouseX - a.tmx) &&
                        3 > Math.abs(a.mouseY - a.tmy)) && a.fire({type: "click", x: a.mouseX, y: a.mouseY, chart: a});
                    a.mapContainerClickX = NaN;
                    a.mapContainerClickY = NaN;
                    a.objectWasClicked = !1;
                    a.zoomOnDoubleClick && a.mouseIsOver && (b = (new Date).getTime(), 200 > b - a.previousClickTime && 40 < b - a.previousClickTime && a.doDoubleClickZoom(), a.previousClickTime = b)
                }
                a.wasTouched = !1
            }
        }, resetPinch: function () {
            this.mapWasPinched = !1
        }, handleMouseMove: function (a) {
            var b = this;
            e.AmMap.base.handleMouseMove.call(b, a);
            if (!a || !a.touches || !b.tapToActivate || b.tapped) {
                b.panEventsEnabled &&
                    b.mouseIsOver && a && a.preventDefault && a.preventDefault();
                var c = b.previuosMouseX, d = b.previuosMouseY, f = b.mouseX, h = b.mouseY, g = b.zoomControl;
                isNaN(c) && (c = f);
                isNaN(d) && (d = h);
                b.mouse2X = NaN;
                b.mouse2Y = NaN;
                a && a.touches && (a = a.touches.item(1)) && b.panEventsEnabled && b.boundingRect && (b.mouse2X = a.clientX - b.boundingRect.left, b.mouse2Y = a.clientY - b.boundingRect.top);
                if (a = b.mapContainer) {
                    var k = b.mouse2X, l = b.mouse2Y;
                    b.pinchTO && clearTimeout(b.pinchTO);
                    b.pinchTO = setTimeout(function () {
                        b.resetPinch.call(b)
                    }, 1E3);
                    var m = b.realHeight,
                        n = b.realWidth, p = b.mapWidth, r = b.mapHeight;
                    b.mouseIsDown && b.dragMap && (3 < Math.abs(b.previuosMouseX - b.mouseX) || 3 < Math.abs(b.previuosMouseY - b.mouseY)) && (b.isDragging = !0);
                    if (!isNaN(k)) {
                        b.stopDrag();
                        var t = Math.sqrt(Math.pow(k - f, 2) + Math.pow(l - h, 2)), q = b.initialDistance;
                        isNaN(q) && (q = Math.sqrt(Math.pow(k - f, 2) + Math.pow(l - h, 2)));
                        if (!isNaN(q)) {
                            var k = b.initialTouchZoom * t / q, k = e.fitToBounds(k, g.minZoomLevel, g.maxZoomLevel), g = b.zoomLevel(), q = b.middleXP, l = b.middleYP, t = m / r, u = n / p, q = (b.zoomX() - q * u) * (k / g) + q * u, l = (b.zoomY() -
                                l * t) * (k / g) + l * t;
                            .1 < Math.abs(k - g) && (b.zoomTo(k, q, l, !0), b.mapWasPinched = !0, clearTimeout(b.pinchTO))
                        }
                    }
                    k = a.scale;
                    b.isDragging && (b.balloon.hide(0), b.positionChanged(), c = a.x + (f - c), d = a.y + (h - d), b.preventDragOut && (r = -r * k + m / 2 - b.diffY * b.mapScale * k, m = m / 2 - b.diffY * b.mapScale * k, c = e.fitToBounds(c, -p * k + n / 2, n / 2), d = e.fitToBounds(d, r, m)), isNaN(c) || isNaN(d) || (a.translate(c, d, k, !0), b.updateSmallMap()));
                    b.previuosMouseX = f;
                    b.previuosMouseY = h
                }
            }
        }, selectObject: function (a, b) {
            var c = this;
            a || (a = c.dataProvider);
            a.isOver = !1;
            var d =
                a.linkToObject;
            e.isString(d) && (d = c.getObjectById(d));
            a.useTargetsZoomValues && d && (a.zoomX = d.zoomX, a.zoomY = d.zoomY, a.zoomLatitude = d.zoomLatitude, a.zoomLongitude = d.zoomLongitude, a.zoomLevel = d.zoomLevel);
            var f = c.selectedObject;
            f && c.returnInitialColor(f);
            c.selectedObject = a;
            var h = !1, g, k;
            "MapArea" == a.objectType && (a.autoZoomReal && (h = !0), g = c.areasSettings.selectedOutlineColor, k = c.areasSettings.selectedOutlineThickness);
            if (d && !h && (e.isString(d) && (d = c.getObjectById(d)), isNaN(a.zoomLevel) && isNaN(a.zoomX) && isNaN(a.zoomY))) {
                if (c.extendMapData(d))return;
                c.selectObject(d);
                return
            }
            c.allowMultipleDescriptionWindows || c.closeAllDescriptions();
            clearTimeout(c.selectedObjectTimeOut);
            clearTimeout(c.processObjectsTimeOut);
            d = c.zoomDuration;
            !h && isNaN(a.zoomLevel) && isNaN(a.zoomX) && isNaN(a.zoomY) ? (c.showDescriptionAndGetUrl(), b || c.processObjects()) : (c.selectedObjectTimeOut = setTimeout(function () {
                c.showDescriptionAndGetUrl.call(c)
            }, 1E3 * d + 200), c.showObjectsAfterZoom) ? b || (c.processObjectsTimeOut = setTimeout(function () {
                c.processObjects.call(c)
            }, 1E3 * d + 200)) : b || c.processObjects();
            d = a.displayObject;
            h = a.selectedColorReal;
            if ("MapImage" == a.objectType) {
                g = c.imagesSettings.selectedOutlineColor;
                k = c.imagesSettings.selectedOutlineThickness;
                var d = a.image, l = a.selectedScaleReal;
                if (!isNaN(l) && 1 != l) {
                    var m = a.scale;
                    isNaN(a.tempScale) || (m = a.tempScale);
                    isNaN(m) && (m = 1);
                    a.tempScale = m;
                    var n = a.displayObject;
                    n.translate(n.x, n.y, m * l, !0)
                }
            }
            if (d) {
                if (e.removeCN(c, d, "selected-object"), e.setCN(c, d, "selected-object"), a.bringForwardOnHover && a.displayObject.toFront(), c.outlinesToFront(), !a.preserveOriginalAttributes) {
                    d.setAttr("stroke",
                        a.outlineColorReal);
                    void 0 !== h && d.setAttr("fill", h);
                    void 0 !== g && d.setAttr("stroke", g);
                    void 0 !== k && d.setAttr("stroke-width", k);
                    "MapLine" == a.objectType && ((l = a.lineSvg) && l.setAttr("stroke", h), l = a.arrowSvg) && (l.setAttr("fill", h), l.setAttr("stroke", h));
                    if (l = a.imageLabel)m = a.selectedLabelColorReal, void 0 !== m && l.setAttr("fill", m);
                    a.selectable || (d.setAttr("cursor", "default"), l && l.setAttr("cursor", "default"))
                }
            } else c.returnInitialColorReal(a);
            if (d = a.groupId)for (l = a.groupArray, l || (l = c.getGroupById(d), a.groupArray =
                l), m = 0; m < l.length; m++)if (n = l[m], n.isOver = !1, d = n.displayObject, "MapImage" == n.objectType && (d = n.image), d) {
                var p = n.selectedColorReal;
                void 0 !== p && d.setAttr("fill", p);
                void 0 !== g && d.setAttr("stroke", g);
                void 0 !== k && d.setAttr("stroke-width", k);
                "MapLine" == n.objectType && ((d = n.lineSvg) && d.setAttr("stroke", h), d = n.arrowSvg) && (d.setAttr("fill", h), d.setAttr("stroke", h))
            }
            c.rescaleObjects();
            c.zoomToSelectedObject();
            f != a && c.fire({type: "selectedObjectChanged", chart: c})
        }, returnInitialColor: function (a, b) {
            this.returnInitialColorReal(a);
            b && (a.isFirst = !1);
            if (this.selectedObject.bringForwardOnHover) {
                var c = this.selectedObject.displayObject;
                c && c.toFront()
            }
            if (c = a.groupId) {
                var c = this.getGroupById(c), d;
                for (d = 0; d < c.length; d++)this.returnInitialColorReal(c[d]), b && (c[d].isFirst = !1)
            }
            this.outlinesToFront()
        }, outlinesToFront: function () {
            if (this.outlines)for (var a = 0; a < this.outlines.length; a++)this.outlines[a].toFront()
        }, closeAllDescriptions: function () {
            this.descriptionsDiv.innerHTML = ""
        }, fireClosed: function () {
            this.fire({type: "descriptionClosed", chart: this})
        },
        returnInitialColorReal: function (a) {
            a.isOver = !1;
            var b = a.displayObject;
            if (b) {
                e.removeCN(this, b, "selected-object");
                b.toPrevious();
                if ("MapImage" == a.objectType) {
                    var c = a.tempScale;
                    isNaN(c) || b.translate(b.x, b.y, c, !0);
                    a.tempScale = NaN;
                    b = a.image
                }
                c = a.colorReal;
                if ("MapLine" == a.objectType) {
                    var d = a.lineSvg;
                    d && d.setAttr("stroke", c);
                    if (d = a.arrowSvg) {
                        var f = a.arrowColor;
                        void 0 === f && (f = c);
                        d.setAttr("fill", f);
                        d.setAttr("stroke", f)
                    }
                }
                var d = a.alphaReal, f = a.outlineAlphaReal, h = a.outlineThicknessReal, g = a.outlineColorReal;
                if (a.showAsSelected) {
                    var c = a.selectedColorReal, k, l;
                    "MapImage" == a.objectType && (k = this.imagesSettings.selectedOutlineColor, l = this.imagesSettings.selectedOutlineThickness);
                    "MapArea" == a.objectType && (k = this.areasSettings.selectedOutlineColor, l = this.areasSettings.selectedOutlineThickness);
                    void 0 !== k && (g = k);
                    void 0 !== l && (h = l)
                }
                "bubble" == a.type && (c = void 0);
                void 0 !== c && b.setAttr("fill", c);
                if (k = a.image)k.setAttr("fill", c), k.setAttr("stroke", g), k.setAttr("stroke-width", h), k.setAttr("fill-opacity", d), k.setAttr("stroke-opacity",
                    f);
                "MapArea" == a.objectType && (c = 1, this.areasSettings.adjustOutlineThickness && (c = this.zoomLevel() * this.mapScale), b.setAttr("stroke", g), b.setAttr("stroke-width", h / c), b.setAttr("fill-opacity", d), b.setAttr("stroke-opacity", f));
                (c = a.pattern) && b.pattern(c, this.mapScale, this.path);
                (b = a.imageLabel) && !a.labelInactive && (a.showAsSelected && void 0 !== a.selectedLabelColor ? b.setAttr("fill", a.selectedLabelColor) : b.setAttr("fill", a.labelColorReal))
            }
        }, zoomToRectangle: function (a, b, c, d) {
            var f = this.realWidth, h = this.realHeight,
                g = this.mapSet.scale, k = this.zoomControl, f = e.fitToBounds(c / f > d / h ? .8 * f / (c * g) : .8 * h / (d * g), k.minZoomLevel, k.maxZoomLevel);
            this.zoomToMapXY(f, (a + c / 2) * g, (b + d / 2) * g)
        }, zoomToLatLongRectangle: function (a, b, c, d) {
            var f = this.dataProvider, h = this.zoomControl, g = Math.abs(c - a), k = Math.abs(b - d), l = Math.abs(f.rightLongitude - f.leftLongitude), f = Math.abs(f.topLatitude - f.bottomLatitude), h = e.fitToBounds(g / l > k / f ? .8 * l / g : .8 * f / k, h.minZoomLevel, h.maxZoomLevel);
            this.zoomToLongLat(h, a + (c - a) / 2, d + (b - d) / 2)
        }, getGroupById: function (a) {
            var b =
                [];
            this.getGroup(this.imagesProcessor.allObjects, a, b);
            this.getGroup(this.linesProcessor.allObjects, a, b);
            this.getGroup(this.areasProcessor.allObjects, a, b);
            return b
        }, zoomToGroup: function (a) {
            a = "object" == typeof a ? a : this.getGroupById(a);
            var b, c, d, f, e;
            for (e = 0; e < a.length; e++) {
                var g = a[e].displayObject;
                if (g) {
                    var k = g.getBBox(), g = k.y, l = k.y + k.height, m = k.x, k = k.x + k.width;
                    if (g < b || isNaN(b))b = g;
                    if (l > f || isNaN(f))f = l;
                    if (m < c || isNaN(c))c = m;
                    if (k > d || isNaN(d))d = k
                }
            }
            c += this.diffX;
            d += this.diffX;
            f += this.diffY;
            b += this.diffY;
            this.zoomToRectangle(c, b, d - c, f - b)
        }, getGroup: function (a, b, c) {
            if (a) {
                var d;
                for (d = 0; d < a.length; d++) {
                    var f = a[d];
                    f.groupId == b && c.push(f)
                }
            }
        }, zoomToStageXY: function (a, b, c, d) {
            if (!this.objectWasClicked) {
                var f = this.zoomControl;
                a = e.fitToBounds(a, f.minZoomLevel, f.maxZoomLevel);
                var f = this.zoomLevel(), h = this.mapSet.getBBox();
                b = this.xyToCoordinates((b - this.mapContainer.x) / f - h.x * this.mapScale, (c - this.mapContainer.y) / f - h.y * this.mapScale);
                this.zoomToLongLat(a, b.longitude, b.latitude, d)
            }
        }, zoomToLongLat: function (a, b, c, d) {
            b = this.coordinatesToXY(b, c);
            this.zoomToMapXY(a, b.x, b.y, d)
        }, zoomToMapXY: function (a, b, c, d) {
            var f = this.mapWidth, e = this.mapHeight;
            this.zoomTo(a, -(b / f) * a + this.realWidth / f / 2, -(c / e) * a + this.realHeight / e / 2, d)
        }, zoomToObject: function (a) {
            if (a) {
                var b = a.zoomLatitude, c = a.zoomLongitude;
                isNaN(a.zoomLatitudeC) || (b = a.zoomLatitudeC);
                isNaN(a.zoomLongitudeC) || (c = a.zoomLongitudeC);
                var d = a.zoomLevel, f = this.zoomInstantly, h = a.zoomX, g = a.zoomY, k = this.realWidth, l = this.realHeight;
                isNaN(d) || (isNaN(b) || isNaN(c) ? this.zoomTo(d,
                    h, g, f) : this.zoomToLongLat(d, c, b, f));
                this.zoomInstantly = !1;
                "MapImage" == a.objectType && isNaN(a.zoomX) && isNaN(a.zoomY) && isNaN(a.zoomLatitude) && isNaN(a.zoomLongitude) && !isNaN(a.latitude) && !isNaN(a.longitude) && this.zoomToLongLat(a.zoomLevel, a.longitude, a.latitude);
                "MapArea" == a.objectType && (f = a.displayObject.getBBox(), h = this.mapScale, b = (f.x + this.diffX) * h, c = (f.y + this.diffY) * h, d = f.width * h, f = f.height * h, k = a.autoZoomReal && isNaN(a.zoomLevel) ? d / k > f / l ? .8 * k / d : .8 * l / f : a.zoomLevel, l = this.zoomControl, k = e.fitToBounds(k,
                    l.minZoomLevel, l.maxZoomLevel), isNaN(a.zoomX) && isNaN(a.zoomY) && isNaN(a.zoomLatitude) && isNaN(a.zoomLongitude) && this.zoomToMapXY(k, b + d / 2, c + f / 2));
                this.zoomControl.update()
            }
        }, zoomToSelectedObject: function () {
            this.zoomToObject(this.selectedObject)
        }, zoomTo: function (a, b, c, d) {
            var f = this.zoomControl;
            a = e.fitToBounds(a, f.minZoomLevel, f.maxZoomLevel);
            f = this.zoomLevel();
            isNaN(b) && (b = this.realWidth / this.mapWidth, b = (this.zoomX() - .5 * b) * (a / f) + .5 * b);
            isNaN(c) && (c = this.realHeight / this.mapHeight, c = (this.zoomY() - .5 * c) *
                (a / f) + .5 * c);
            this.stopAnimation();
            isNaN(a) || (f = this.mapContainer, this.initialX = f.x, this.initialY = f.y, this.initialScale = f.scale, this.finalX = this.mapWidth * b, this.finalY = this.mapHeight * c, this.finalScale = a, this.finalX != this.initialX || this.finalY != this.initialY || this.finalScale != this.initialScale ? d ? (this.tweenPercent = 1, this.rescaleMapAndObjects(), this.wheelBusy = !1) : this.animateMap() : this.wheelBusy = !1)
        }, loadXml: function (a) {
            var b;
            window.XMLHttpRequest && (b = new XMLHttpRequest);
            b.overrideMimeType && b.overrideMimeType("text/xml");
            b.open("GET", a, !1);
            b.send();
            this.parseXMLObject(b.responseXML);
            this.svgData && this.buildEverything()
        }, stopAnimation: function () {
            this.frame = this.totalFrames
        }, processObjects: function (a) {
            var b = this.selectedObject;
            if (0 < b.images.length || 0 < b.areas.length || 0 < b.lines.length || b == this.dataProvider || a) {
                a = this.container;
                var c = this.stageImagesContainer;
                c && c.remove();
                this.stageImagesContainer = c = a.set();
                this.trendLinesSet.push(c);
                var d = this.stageLinesContainer;
                d && d.remove();
                this.stageLinesContainer = d = a.set();
                this.trendLinesSet.push(d);
                var f = this.mapImagesContainer;
                f && f.remove();
                this.mapImagesContainer = f = a.set();
                this.mapContainer.push(f);
                var e = this.mapLinesContainer;
                e && e.remove();
                this.mapLinesContainer = e = a.set();
                this.mapContainer.push(e);
                this.linesAboveImages ? (f.toFront(), c.toFront(), e.toFront(), d.toFront()) : (e.toFront(), d.toFront(), f.toFront(), c.toFront());
                b && (this.imagesProcessor.reset(), this.linesProcessor.reset(), this.linesAboveImages ? (this.imagesProcessor.process(b), this.linesProcessor.process(b)) : (this.linesProcessor.process(b),
                    this.imagesProcessor.process(b)));
                this.rescaleObjects()
            }
        }, processAreas: function () {
            this.areasProcessor.process(this.dataProvider)
        }, buildSVGMap: function () {
            e.remove(this.mapSet);
            var a = this.svgData.g.path, b = this.container, c = b.set();
            this.svgAreas = [];
            this.svgAreasById = {};
            void 0 === a.length && (a = [a]);
            var d;
            for (d = 0; d < a.length; d++) {
                var f = a[d], h = f.d, g = f.title;
                f.titleTr && (g = f.titleTr);
                var k = b.path(h);
                k.id = f.id;
                if (this.areasSettings.preserveOriginalAttributes) {
                    k.customAttr = {};
                    for (var l in f)"d" != l && "id" != l && "title" !=
                        l && (k.customAttr[l] = f[l])
                }
                f.outline && (k.outline = !0);
                k.path = h;
                this.svgAreasById[f.id] = {area: k, title: g, className: f["class"]};
                this.svgAreas.push(k);
                c.push(k)
            }
            this.mapSet = c;
            this.mapContainer.push(c);
            this.resizeMap()
        }, centerAlign: function () {
        }, setProjection: function (a) {
            this.projection = a;
            this.chartCreated = !1;
            this.buildEverything()
        }, addObjectEventListeners: function (a, b) {
            var c = this;
            a.mousedown(function (a) {
                c.mouseDownMapObject(b, a)
            }).mouseup(function (a) {
                c.clickMapObject(b, a)
            }).mouseover(function (a) {
                c.balloonX =
                    NaN;
                c.rollOverMapObject(b, !0, a)
            }).mouseout(function (a) {
                    c.balloonX = NaN;
                    c.rollOutMapObject(b, a)
                }).touchend(function (a) {
                    4 > Math.abs(c.mouseX - c.tmx) && 4 > Math.abs(c.mouseY - c.tmy) && (c.tapped = !0);
                    c.tapToActivate && !c.tapped || c.mapWasDragged || c.mapWasPinched || (c.balloonX = NaN, c.rollOverMapObject(b, !0, a), c.clickMapObject(b, a))
                }).touchstart(function (a) {
                    c.tmx = c.mouseX;
                    c.tmy = c.mouseY;
                    c.mouseDownMapObject(b, a)
                }).keyup(function (a) {
                    13 == a.keyCode && c.clickMapObject(b, a)
                })
        }, checkIfSelected: function (a) {
            var b = this.selectedObject;
            if (b == a)return!0;
            if (b = b.groupId) {
                var b = this.getGroupById(b), c;
                for (c = 0; c < b.length; c++)if (b[c] == a)return!0
            }
            return!1
        }, clearMap: function () {
            this.chartDiv.innerHTML = "";
            this.clearObjectList()
        }, clearObjectList: function () {
            var a = this.objectList;
            a && a.div && (a.div.innerHTML = "")
        }, checkIfLast: function (a) {
            if (a) {
                var b = a.parentNode;
                if (b && b.lastChild == a)return!0
            }
            return!1
        }, showAsRolledOver: function (a) {
            var b = a.displayObject;
            if (!a.showAsSelected && b && !a.isOver) {
                b.node.onmouseout = function () {
                };
                b.node.onmouseover = function () {
                };
                b.node.onclick = function () {
                };
                !a.isFirst && a.bringForwardOnHover && (b.toFront(), a.isFirst = !0);
                var c = a.rollOverColorReal, d;
                a.preserveOriginalAttributes && (c = void 0);
                "bubble" == a.type && (c = void 0);
                void 0 == c && (isNaN(a.rollOverBrightnessReal) || (c = e.adjustLuminosity(a.colorReal, a.rollOverBrightnessReal / 100)));
                if (void 0 != c)if ("MapImage" == a.objectType)(d = a.image) && d.setAttr("fill", c); else if ("MapLine" == a.objectType) {
                    if ((d = a.lineSvg) && d.setAttr("stroke", c), d = a.arrowSvg)d.setAttr("fill", c), d.setAttr("stroke", c)
                } else b.setAttr("fill",
                    c);
                (c = a.imageLabel) && !a.labelInactive && (d = a.labelRollOverColorReal, void 0 != d && c.setAttr("fill", d));
                c = a.rollOverOutlineColorReal;
                void 0 != c && ("MapImage" == a.objectType ? (d = a.image) && d.setAttr("stroke", c) : b.setAttr("stroke", c));
                "MapImage" == a.objectType ? (c = this.imagesSettings.rollOverOutlineThickness, (d = a.image) && (isNaN(c) || d.setAttr("stroke-width", c))) : (c = this.areasSettings.rollOverOutlineThickness, isNaN(c) || b.setAttr("stroke-width", c));
                if ("MapArea" == a.objectType) {
                    c = this.areasSettings;
                    d = a.rollOverAlphaReal;
                    isNaN(d) || b.setAttr("fill-opacity", d);
                    d = c.rollOverOutlineAlpha;
                    isNaN(d) || b.setAttr("stroke-opacity", d);
                    d = 1;
                    this.areasSettings.adjustOutlineThickness && (d = this.zoomLevel() * this.mapScale);
                    var f = c.rollOverOutlineThickness;
                    isNaN(f) || b.setAttr("stroke-width", f / d);
                    (c = c.rollOverPattern) && b.pattern(c, this.mapScale, this.path)
                }
                "MapImage" == a.objectType && (c = a.rollOverScaleReal, isNaN(c) || 1 == c || (d = b.scale, isNaN(d) && (d = 1), a.tempScale = d, b.translate(b.x, b.y, d * c, !0)));
                this.useHandCursorOnClickableOjects && this.checkIfClickable(a) &&
                b.setAttr("cursor", "pointer");
                a.mouseEnabled && this.addObjectEventListeners(b, a);
                a.isOver = !0
            }
            this.outlinesToFront()
        }, rollOverMapObject: function (a, b, c) {
            if (this.chartCreated) {
                this.handleMouseMove();
                var d = this.previouslyHovered;
                d && d != a ? (!1 === this.checkIfSelected(d) && (this.returnInitialColor(d, !0), this.previouslyHovered = null), this.balloon.hide(0)) : clearTimeout(this.hoverInt);
                if (!this.preventHover) {
                    if (!1 === this.checkIfSelected(a)) {
                        if (d = a.groupId) {
                            var d = this.getGroupById(d), f;
                            for (f = 0; f < d.length; f++)d[f] !=
                                a && this.showAsRolledOver(d[f])
                        }
                        this.showAsRolledOver(a)
                    } else(d = a.displayObject) && (this.allowClickOnSelectedObject ? d.setAttr("cursor", "pointer") : d.setAttr("cursor", "default"));
                    this.showDescriptionOnHover ? this.showDescription(a) : !this.showBalloonOnSelectedObject && this.checkIfSelected(a) || !1 === b || (f = this.balloon, this.balloon.fixedPosition = !1, b = a.colorReal, d = "", void 0 !== b && this.useObjectColorForBalloon || (b = f.fillColor), (f = a.balloonTextReal) && (d = this.formatString(f, a)), this.balloonLabelFunction && (d = this.balloonLabelFunction(a,
                        this)), d && "" !== d && this.showBalloon(d, b, !1, this.balloonX, this.balloonY));
                    this.fire({type: "rollOverMapObject", mapObject: a, chart: this, event: c});
                    this.previouslyHovered = a
                }
            }
        }, longitudeToX: function (a) {
            return(this.longitudeToCoordinate(a) + this.diffX * this.mapScale) * this.zoomLevel() + this.mapContainer.x
        }, latitudeToY: function (a) {
            return(this.latitudeToCoordinate(a) + this.diffY * this.mapScale) * this.zoomLevel() + this.mapContainer.y
        }, latitudeToStageY: function (a) {
            return this.latitudeToCoordinate(a) * this.zoomLevel() +
                this.mapContainer.y + this.diffY * this.mapScale
        }, longitudeToStageX: function (a) {
            return this.longitudeToCoordinate(a) * this.zoomLevel() + this.mapContainer.x + this.diffX * this.mapScale
        }, stageXToLongitude: function (a) {
            a = (a - this.mapContainer.x) / this.zoomLevel();
            return this.coordinateToLongitude(a)
        }, stageYToLatitude: function (a) {
            a = (a - this.mapContainer.y) / this.zoomLevel();
            return this.coordinateToLatitude(a)
        }, rollOutMapObject: function (a, b) {
            this.hideBalloon();
            a && this.chartCreated && a.isOver && (this.checkIfSelected(a) ||
                this.returnInitialColor(a), this.fire({type: "rollOutMapObject", mapObject: a, chart: this, event: b}))
        }, formatString: function (a, b) {
            var c = this.nf, d = this.pf, f = b.title;
            b.titleTr && (f = b.titleTr);
            void 0 == f && (f = "");
            var h = b.value, h = isNaN(h) ? "" : e.formatNumber(h, c), c = b.percents, c = isNaN(c) ? "" : e.formatNumber(c, d), d = b.description;
            void 0 == d && (d = "");
            var g = b.customData;
            void 0 == g && (g = "");
            return a = e.massReplace(a, {"[[title]]": f, "[[value]]": h, "[[percent]]": c, "[[description]]": d, "[[customData]]": g})
        }, mouseDownMapObject: function (a, b) {
            this.fire({type: "mouseDownMapObject", mapObject: a, chart: this, event: b})
        }, clickMapObject: function (a, b) {
            var c = this;
            b && (b.touches || isNaN(a.zoomLevel) && isNaN(a.zoomX) && isNaN(a.zoomY) || c.hideBalloon());
            if (c.chartCreated && !c.preventHover && c.checkTouchDuration(b) && !c.mapWasDragged && c.checkIfClickable(a) && !c.mapWasPinched) {
                c.selectObject(a);
                var d = c.zoomLevel(), f = c.mapSet.getBBox(), d = c.xyToCoordinates((c.mouseX - c.mapContainer.x) / d - f.x * c.mapScale, (c.mouseY - c.mapContainer.y) / d - f.y * c.mapScale);
                c.clickLatitude =
                    d.latitude;
                c.clickLongitude = d.longitude;
                b && b.touches && setTimeout(function () {
                    c.showBalloonAfterZoom.call(c)
                }, 1E3 * c.zoomDuration);
                c.fire({type: "clickMapObject", mapObject: a, chart: c, event: b});
                c.objectWasClicked = !0
            }
        }, showBalloonAfterZoom: function () {
            var a = this.clickLongitude, b = this.clickLatitude, c = this.selectedObject;
            "MapImage" != c.objectType || isNaN(c.longitude) || (a = c.longitude, b = c.latitude);
            a = this.coordinatesToStageXY(a, b);
            this.balloonX = a.x;
            this.balloonY = a.y;
            this.rollOverMapObject(this.selectedObject, !0)
        },
        checkIfClickable: function (a) {
            var b = this.allowClickOnSelectedObject;
            return this.selectedObject == a && b ? !0 : this.selectedObject != a || b ? !0 === a.selectable || "MapArea" == a.objectType && a.autoZoomReal || a.url || a.linkToObject || 0 < a.images.length || 0 < a.lines.length || !isNaN(a.zoomLevel) || !isNaN(a.zoomX) || !isNaN(a.zoomY) || a.description ? !0 : !1 : !1
        }, resizeMap: function () {
            var a = this.mapSet;
            if (a) {
                var b = 1, c = a.getBBox(), d = this.realWidth, f = this.realHeight, e = c.width, c = c.height;
                0 < e && 0 < c && (this.fitMapToContainer && (b = e / d > c / f ? d / e :
                    f / c), a.translate(0, 0, b, !0), this.mapScale = b, this.mapHeight = c * b, this.mapWidth = e * b)
            }
        }, zoomIn: function () {
            var a = this.zoomLevel() * this.zoomControl.zoomFactor;
            this.zoomTo(a)
        }, zoomOut: function () {
            var a = this.zoomLevel() / this.zoomControl.zoomFactor;
            this.zoomTo(a)
        }, moveLeft: function () {
            var a = this.zoomX() + this.zoomControl.panStepSize;
            this.zoomTo(this.zoomLevel(), a, this.zoomY())
        }, moveRight: function () {
            var a = this.zoomX() - this.zoomControl.panStepSize;
            this.zoomTo(this.zoomLevel(), a, this.zoomY())
        }, moveUp: function () {
            var a =
                this.zoomY() + this.zoomControl.panStepSize;
            this.zoomTo(this.zoomLevel(), this.zoomX(), a)
        }, moveDown: function () {
            var a = this.zoomY() - this.zoomControl.panStepSize;
            this.zoomTo(this.zoomLevel(), this.zoomX(), a)
        }, zoomX: function () {
            return this.mapSet ? Math.round(1E4 * this.mapContainer.x / this.mapWidth) / 1E4 : NaN
        }, zoomY: function () {
            return this.mapSet ? Math.round(1E4 * this.mapContainer.y / this.mapHeight) / 1E4 : NaN
        }, goHome: function () {
            this.selectObject(this.dataProvider);
            this.fire({type: "homeButtonClicked", chart: this})
        }, zoomLevel: function () {
            return Math.round(1E5 *
                this.mapContainer.scale) / 1E5
        }, showDescriptionAndGetUrl: function () {
            var a = this.selectedObject;
            if (a) {
                this.showDescription();
                var b = a.url;
                if (b)e.getURL(b, a.urlTarget); else if (b = a.linkToObject) {
                    if (e.isString(b)) {
                        var c = this.getObjectById(b);
                        if (c) {
                            this.selectObject(c);
                            return
                        }
                    }
                    b && a.passZoomValuesToTarget && (b.zoomLatitude = this.zoomLatitude(), b.zoomLongitude = this.zoomLongitude(), b.zoomLevel = this.zoomLevel());
                    this.extendMapData(b) || this.selectObject(b)
                }
            }
        }, extendMapData: function (a) {
            var b = a.objectType;
            if ("MapImage" !=
                b && "MapArea" != b && "MapLine" != b)return e.extend(a, new e.MapData, !0), this.dataProvider = a, this.zoomInstantly = !0, this.validateData(), !0
        }, showDescription: function (a) {
            a || (a = this.selectedObject);
            this.allowMultipleDescriptionWindows || this.closeAllDescriptions();
            if (a.description) {
                var b = a.descriptionWindow;
                b && b.close();
                b = new e.DescriptionWindow;
                a.descriptionWindow = b;
                var c = a.descriptionWindowWidth, d = a.descriptionWindowHeight, f = a.descriptionWindowLeft, h = a.descriptionWindowTop, g = a.descriptionWindowRight, k = a.descriptionWindowBottom;
                isNaN(g) || (f = this.realWidth - g);
                isNaN(k) || (h = this.realHeight - k);
                var l = a.descriptionWindowX;
                isNaN(l) || (f = l);
                l = a.descriptionWindowY;
                isNaN(l) || (h = l);
                isNaN(f) && (f = this.mouseX, f = f > this.realWidth / 2 ? f - c - 20 : f + 20);
                isNaN(h) && (h = this.mouseY);
                b.maxHeight = d;
                l = a.title;
                a.titleTr && (l = a.titleTr);
                b.show(this, this.descriptionsDiv, a.description, l);
                a = b.div.style;
                a.position = "absolute";
                a.width = c + "px";
                a.maxHeight = d + "px";
                isNaN(k) || (h -= b.div.offsetHeight);
                isNaN(g) || (f -= b.div.offsetWidth);
                a.left = f + "px";
                a.top = h + "px"
            }
        }, parseXMLObject: function (a) {
            var b =
            {root: {}};
            this.parseXMLNode(b, "root", a);
            this.svgData = b.root.svg;
            this.getBounds()
        }, getBounds: function () {
            var a = this.dataProvider;
            try {
                var b = this.svgData.defs["amcharts:ammap"];
                a.leftLongitude = Number(b.leftLongitude);
                a.rightLongitude = Number(b.rightLongitude);
                a.topLatitude = Number(b.topLatitude);
                a.bottomLatitude = Number(b.bottomLatitude);
                a.projection = b.projection;
                var c = b.wrappedLongitudes;
                c && (a.rightLongitude += 360);
                a.wrappedLongitudes = c
            } catch (d) {
            }
        }, recalcLongitude: function (a) {
            return this.dataProvider.wrappedLongitudes ?
                a < this.dataProvider.leftLongitude ? Number(a) + 360 : a : a
        }, latitudeToCoordinate: function (a) {
            var b, c = this.dataProvider;
            if (this.mapSet) {
                b = c.topLatitude;
                var d = c.bottomLatitude;
                "mercator" == c.projection && (a = this.mercatorLatitudeToCoordinate(a), b = this.mercatorLatitudeToCoordinate(b), d = this.mercatorLatitudeToCoordinate(d));
                b = (a - b) / (d - b) * this.mapHeight
            }
            return b
        }, longitudeToCoordinate: function (a) {
            a = this.recalcLongitude(a);
            var b, c = this.dataProvider;
            this.mapSet && (b = c.leftLongitude, b = (a - b) / (c.rightLongitude - b) * this.mapWidth);
            return b
        }, mercatorLatitudeToCoordinate: function (a) {
            89.5 < a && (a = 89.5);
            -89.5 > a && (a = -89.5);
            a = e.degreesToRadians(a);
            return e.radiansToDegrees(.5 * Math.log((1 + Math.sin(a)) / (1 - Math.sin(a))) / 2)
        }, zoomLatitude: function () {
            if (this.mapContainer) {
                var a = this.mapSet.getBBox(), b = (-this.mapContainer.x + this.previousWidth / 2) / this.zoomLevel() - a.x * this.mapScale, a = (-this.mapContainer.y + this.previousHeight / 2) / this.zoomLevel() - a.y * this.mapScale;
                return this.xyToCoordinates(b, a).latitude
            }
        }, zoomLongitude: function () {
            if (this.mapContainer) {
                var a =
                    this.mapSet.getBBox(), b = (-this.mapContainer.x + this.previousWidth / 2) / this.zoomLevel() - a.x * this.mapScale, a = (-this.mapContainer.y + this.previousHeight / 2) / this.zoomLevel() - a.y * this.mapScale;
                return this.xyToCoordinates(b, a).longitude
            }
        }, getAreaCenterLatitude: function (a) {
            a = a.displayObject.getBBox();
            var b = this.mapScale, c = this.mapSet.getBBox();
            return this.xyToCoordinates((a.x + a.width / 2 + this.diffX) * b - c.x * b, (a.y + a.height / 2 + this.diffY) * b - c.y * b).latitude
        }, getAreaCenterLongitude: function (a) {
            a = a.displayObject.getBBox();
            var b = this.mapScale, c = this.mapSet.getBBox();
            return this.xyToCoordinates((a.x + a.width / 2 + this.diffX) * b - c.x * b, (a.y + a.height / 2 + this.diffY) * b - c.y * b).longitude
        }, milesToPixels: function (a) {
            var b = this.dataProvider;
            return this.mapWidth / (b.rightLongitude - b.leftLongitude) * a / 69.172
        }, kilometersToPixels: function (a) {
            var b = this.dataProvider;
            return this.mapWidth / (b.rightLongitude - b.leftLongitude) * a / 111.325
        }, handleBackgroundClick: function () {
            if (this.backgroundZoomsToTop && !this.mapWasDragged) {
                var a = this.dataProvider;
                if (this.checkIfClickable(a))this.clickMapObject(a); else {
                    var b = a.zoomX, c = a.zoomY, d = a.zoomLongitude, f = a.zoomLatitude, a = a.zoomLevel;
                    isNaN(b) || isNaN(c) || this.zoomTo(a, b, c);
                    isNaN(d) || isNaN(f) || this.zoomToLongLat(a, d, f, !0)
                }
            }
        }, parseXMLNode: function (a, b, c, d) {
            void 0 === d && (d = "");
            var f, e, g;
            if (c) {
                var k = c.childNodes.length;
                for (f = 0; f < k; f++) {
                    e = c.childNodes[f];
                    var l = e.nodeName, m = e.nodeValue ? this.trim(e.nodeValue) : "", n = !1;
                    e.attributes && 0 < e.attributes.length && (n = !0);
                    if (0 !== e.childNodes.length || "" !== m || !1 !== n)if (3 ==
                        e.nodeType || 4 == e.nodeType) {
                        if ("" !== m) {
                            e = 0;
                            for (g in a[b])a[b].hasOwnProperty(g) && e++;
                            e ? a[b]["#text"] = m : a[b] = m
                        }
                    } else if (1 == e.nodeType) {
                        var p;
                        void 0 !== a[b][l] ? void 0 === a[b][l].length ? (p = a[b][l], a[b][l] = [], a[b][l].push(p), a[b][l].push({}), p = a[b][l][1]) : "object" == typeof a[b][l] && (a[b][l].push({}), p = a[b][l][a[b][l].length - 1]) : (a[b][l] = {}, p = a[b][l]);
                        if (e.attributes && e.attributes.length)for (m = 0; m < e.attributes.length; m++)p[e.attributes[m].name] = e.attributes[m].value;
                        void 0 !== a[b][l].length ? this.parseXMLNode(a[b][l],
                            a[b][l].length - 1, e, d + "  ") : this.parseXMLNode(a[b], l, e, d + "  ")
                    }
                }
                e = 0;
                c = "";
                for (g in a[b])"#text" == g ? c = a[b][g] : e++;
                0 === e && void 0 === a[b].length && (a[b] = c)
            }
        }, doDoubleClickZoom: function () {
            if (!this.mapWasDragged) {
                var a = this.zoomLevel() * this.zoomControl.zoomFactor;
                this.zoomToStageXY(a, this.mouseX, this.mouseY)
            }
        }, getDevInfo: function () {
            var a = this.zoomLevel(), b = this.mapSet.getBBox(), b = this.xyToCoordinates((this.mouseX - this.mapContainer.x) / a - b.x * this.mapScale, (this.mouseY - this.mapContainer.y) / a - b.y * this.mapScale),
                a = {chart: this, type: "writeDevInfo", zoomLevel: a, zoomX: this.zoomX(), zoomY: this.zoomY(), zoomLatitude: this.zoomLatitude(), zoomLongitude: this.zoomLongitude(), latitude: b.latitude, longitude: b.longitude, left: this.mouseX, top: this.mouseY, right: this.realWidth - this.mouseX, bottom: this.realHeight - this.mouseY, percentLeft: Math.round(this.mouseX / this.realWidth * 100) + "%", percentTop: Math.round(this.mouseY / this.realHeight * 100) + "%", percentRight: Math.round((this.realWidth - this.mouseX) / this.realWidth * 100) + "%", percentBottom: Math.round((this.realHeight -
                    this.mouseY) / this.realHeight * 100) + "%"}, b = "zoomLevel:" + a.zoomLevel + ", zoomLongitude:" + a.zoomLongitude + ", zoomLatitude:" + a.zoomLatitude + "\n", b = b + ("zoomX:" + a.zoomX + ", zoomY:" + a.zoomY + "\n"), b = b + ("latitude:" + a.latitude + ", longitude:" + a.longitude + "\n"), b = b + ("left:" + a.left + ", top:" + a.top + "\n"), b = b + ("right:" + a.right + ", bottom:" + a.bottom + "\n"), b = b + ("left:" + a.percentLeft + ", top:" + a.percentTop + "\n"), b = b + ("right:" + a.percentRight + ", bottom:" + a.percentBottom + "\n");
            a.str = b;
            this.fire(a);
            return a
        }, getXY: function (a, b, c) {
            void 0 !== a && (-1 != String(a).indexOf("%") ? (a = Number(a.split("%").join("")), c && (a = 100 - a), a = Number(a) * b / 100) : c && (a = b - a));
            return a
        }, getObjectById: function (a) {
            var b = this.dataProvider;
            if (b.areas) {
                var c = this.getObject(a, b.areas);
                if (c)return c
            }
            if (c = this.getObject(a, b.images))return c;
            if (a = this.getObject(a, b.lines))return a
        }, getObject: function (a, b) {
            if (b) {
                var c;
                for (c = 0; c < b.length; c++) {
                    var d = b[c];
                    if (d.id == a)return d;
                    if (d.areas) {
                        var f = this.getObject(a, d.areas);
                        if (f)return f
                    }
                    if (f = this.getObject(a, d.images))return f;
                    if (d = this.getObject(a, d.lines))return d
                }
            }
        }, parseData: function () {
            var a = this.dataProvider;
            this.processObject(a.areas, a, "area");
            this.processObject(a.images, a, "image");
            this.processObject(a.lines, a, "line")
        }, processObject: function (a, b, c) {
            if (a) {
                var d;
                for (d = 0; d < a.length; d++) {
                    var f = a[d];
                    f.parentObject = b;
                    "area" == c && e.extend(f, new e.MapArea(this.theme), !0);
                    "image" == c && (f = e.extend(f, new e.MapImage(this.theme), !0));
                    "line" == c && (f = e.extend(f, new e.MapLine(this.theme), !0));
                    a[d] = f;
                    f.areas && this.processObject(f.areas,
                        f, "area");
                    f.images && this.processObject(f.images, f, "image");
                    f.lines && this.processObject(f.lines, f, "line")
                }
            }
        }, positionChanged: function () {
            var a = {type: "positionChanged", zoomX: this.zoomX(), zoomY: this.zoomY(), zoomLevel: this.zoomLevel(), chart: this};
            this.fire(a)
        }, getX: function (a, b) {
            return this.getXY(a, this.realWidth, b)
        }, getY: function (a, b) {
            return this.getXY(a, this.realHeight, b)
        }, trim: function (a) {
            if (a) {
                var b;
                for (b = 0; b < a.length; b++)if (-1 === " \n\r\t\f\x0B\u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000".indexOf(a.charAt(b))) {
                    a =
                        a.substring(b);
                    break
                }
                for (b = a.length - 1; 0 <= b; b--)if (-1 === " \n\r\t\f\x0B\u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000".indexOf(a.charAt(b))) {
                    a = a.substring(0, b + 1);
                    break
                }
                return-1 === " \n\r\t\f\x0B\u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000".indexOf(a.charAt(0)) ? a : ""
            }
        }, destroy: function () {
            e.AmMap.base.destroy.call(this)
        }, x2c: function (a) {
            var b = this.dataProvider.leftLongitude;
            return Math.round(this.unscaledMapWidth *
                (a - b) / (this.dataProvider.rightLongitude - b) * 100) / 100
        }, y2c: function (a) {
            var b = this.dataProvider.topLatitude;
            return Math.round(this.unscaledMapHeight * (a - b) / (this.dataProvider.bottomLatitude - b) * 100) / 100
        }, normalize: function (a) {
            if (!a.pathsArray) {
                var b;
                if (a.normalized)b = a.normalized; else {
                    var c = e.normalizePath(a.node);
                    b = a.node.getAttribute("d");
                    a.normalized = b;
                    c.maxX > this.maxMapX && (this.maxMapX = c.maxX);
                    c.minX < this.minMapX && (this.minMapX = c.minX);
                    c.maxY > this.maxMapY && (this.maxMapY = c.maxY);
                    c.minY < this.minMapY &&
                    (this.minMapY = c.minY)
                }
                a.node.setAttribute("d", b)
            }
        }, redraw: function (a) {
            var b = a.normalized, b = b.split(" Z").join(""), b = b.split("M");
            a.pathsArray = [];
            for (var c = 0; c < b.length; c++) {
                var d = b[c];
                if (d) {
                    for (var d = d.split("L"), f = [], e = 0; e < d.length; e++)if (d[e]) {
                        var g = d[e].split(" "), g = this.xyToCoordinates(Number(g[1]), Number(g[2]), this.dpProjectionFunction, this.sourceMapWidth, this.sourceMapHeight);
                        f.push([g.longitude, g.latitude])
                    }
                    a.pathsArray.push(f)
                }
            }
            b = "";
            for (c = 0; c < a.pathsArray.length; c++)b += this.redrawArea(a.pathsArray[c]);
            a.node.setAttribute("d", b);
            a.path = b
        }, redrawArea: function (a) {
            for (var b = !1, c = "", d = 0; d < a.length; d++) {
                var f = a[d][0], h = a[d][1], g = e.degreesToRadians(a[d][0]), k = e.degreesToRadians(a[d][1]), k = this.projectionFunction(g, k), g = e.roundTo(this.x2c(e.radiansToDegrees(k[0])), 3), k = e.roundTo(this.y2c(e.radiansToDegrees(k[1])), 3);
                g < this.minMapXX && (this.minMapXX = g, this.leftLongLat = {longitude: f, latitude: h});
                g > this.maxMapXX && (this.maxMapXX = g, this.rightLongLat = {longitude: f, latitude: h});
                k < this.minMapYY && (this.minMapYY = k,
                    this.topLongLat = {longitude: f, latitude: h});
                k > this.maxMapYY && (this.maxMapYY = k, this.bottomLongLat = {longitude: f, latitude: h});
                b ? c += " L " : (c += " M ", b = !0);
                c += g + " " + k
            }
            return c + " Z "
        }, normalizeMap: function () {
            var a = e.degreesToRadians(this.dataProvider.leftLongitude), b = e.degreesToRadians(this.dataProvider.rightLongitude), c = e.degreesToRadians(this.dataProvider.topLatitude), d = e.degreesToRadians(this.dataProvider.bottomLatitude), f = a + (b - a) / 2, h = c + (d - c) / 2, g = this.dpProjectionFunction(f, c)[1], k = this.dpProjectionFunction(f,
                d)[1], l = this.dpProjectionFunction(a, h)[0], m = this.dpProjectionFunction(b, h)[0], c = e.equirectangular(f, c), d = e.equirectangular(f, d), g = (c[1] - d[1]) / (g - k), a = e.equirectangular(a, h), b = e.equirectangular(b, h), l = (a[0] - b[0]) / (l - m);
            this.minMapX = Infinity;
            this.maxMapX = -Infinity;
            this.minMapY = Infinity;
            this.maxMapY = -Infinity;
            for (m = 0; m < this.svgAreas.length; m++)this.normalize(this.svgAreas[m]);
            if (this.dataProvider.wrappedLongitudes)for (m = 0; m < this.svgAreas.length; m++)this.svgAreas[m].translate(-this.minMapX, -this.minMapY);
            this.sourceMapHeight = Math.abs(this.maxMapY - this.minMapY);
            this.sourceMapWidth = Math.abs(this.maxMapX - this.minMapX);
            this.unscaledMapWidth = this.sourceMapWidth * l;
            this.unscaledMapHeight = this.sourceMapHeight * g;
            this.diffY = this.diffX = 0
        }, fixMapPosition: function () {
            var a = e.degreesToRadians(this.dataProvider.leftLongitude), b = e.degreesToRadians(this.dataProvider.rightLongitude), c = e.degreesToRadians(this.dataProvider.topLatitude), d = e.degreesToRadians(this.dataProvider.bottomLatitude), f = a + (b - a) / 2, h = c + (d - c) / 2, g = this.dpProjectionFunction(f,
                c)[1], k = this.dpProjectionFunction(f, d)[1], l = this.dpProjectionFunction(a, h)[0], m = this.dpProjectionFunction(b, h)[0];
            this.sourceMapHeight = this.mapHeight / this.mapScale;
            this.sourceMapWidth = this.mapWidth / this.mapScale;
            this.unscaledMapWidth = (a - b) / (l - m) * this.sourceMapWidth;
            this.unscaledMapHeight = (c - d) / (g - k) * this.sourceMapHeight;
            b = this.coordinatesToXY(e.radiansToDegrees(f), e.radiansToDegrees(c));
            a = this.coordinatesToXY(e.radiansToDegrees(a), e.radiansToDegrees(h));
            c = h = Infinity;
            for (d = 0; d < this.svgAreas.length; d++)f =
                this.svgAreas[d].getBBox(), f.y < h && (h = f.y), f.x < c && (c = f.x);
            this.diffY = b.y / this.mapScale - h;
            this.diffX = a.x / this.mapScale - c;
            for (d = 0; d < this.svgAreas.length; d++)this.svgAreas[d].translate(this.diffX, this.diffY)
        }, changeProjection: function () {
            this.minMapXX = Infinity;
            this.maxMapXX = -Infinity;
            this.minMapYY = Infinity;
            this.maxMapYY = -Infinity;
            this.projectionChanged = !1;
            for (var a = 0; a < this.svgAreas.length; a++)this.redraw(this.svgAreas[a]);
            this.projectionChanged = !0;
            this.resizeMap()
        }, coordinatesToXY: function (a, b) {
            var c,
                d;
            c = !1;
            this.dataProvider && (c = this.dataProvider.wrappedLongitudes) && (a = this.recalcLongitude(a));
            this.projectionFunction ? (d = this.projectionFunction(e.degreesToRadians(a), e.degreesToRadians(b)), c = this.mapScale * e.roundTo(this.x2c(e.radiansToDegrees(d[0])), 3), d = this.mapScale * e.roundTo(this.y2c(e.radiansToDegrees(d[1])), 3)) : (c = this.longitudeToCoordinate(a), d = this.latitudeToCoordinate(b));
            return{x: c, y: d}
        }, coordinatesToStageXY: function (a, b) {
            var c = this.coordinatesToXY(a, b), d = c.x * this.zoomLevel() + this.mapContainer.x,
                c = c.y * this.zoomLevel() + this.mapContainer.y;
            return{x: d, y: c}
        }, stageXYToCoordinates: function (a, b) {
            var c = this.mapSet.getBBox(), d = (a - this.mapContainer.x) / this.zoomLevel() - c.x * this.mapScale, c = (b - this.mapContainer.y) / this.zoomLevel() - c.y * this.mapScale;
            return this.xyToCoordinates(d, c)
        }, xyToCoordinates: function (a, b, c, d, f) {
            var h;
            isNaN(d) && (d = this.mapWidth);
            isNaN(f) && (f = this.mapHeight);
            c || (c = this.projectionFunction);
            if (h = c.invert) {
                var g = this.dataProvider.leftLongitude, k = this.dataProvider.rightLongitude, l = this.dataProvider.topLatitude,
                    m = this.dataProvider.bottomLatitude, n = g + (k - g) / 2, p = l + (m - l) / 2, l = e.radiansToDegrees(c(e.degreesToRadians(n), e.degreesToRadians(l))[1]), m = e.radiansToDegrees(c(e.degreesToRadians(n), e.degreesToRadians(m))[1]), g = e.radiansToDegrees(c(e.degreesToRadians(g), e.degreesToRadians(p))[0]), k = e.radiansToDegrees(c(e.degreesToRadians(k), e.degreesToRadians(p))[0]);
                this.projectionChanged && (l = e.radiansToDegrees(c(e.degreesToRadians(this.topLongLat.longitude), e.degreesToRadians(this.topLongLat.latitude))[1]), m = e.radiansToDegrees(c(e.degreesToRadians(this.bottomLongLat.longitude),
                    e.degreesToRadians(this.bottomLongLat.latitude))[1]), g = e.radiansToDegrees(c(e.degreesToRadians(this.leftLongLat.longitude), e.degreesToRadians(this.leftLongLat.latitude))[0]), k = e.radiansToDegrees(c(e.degreesToRadians(this.rightLongLat.longitude), e.degreesToRadians(this.rightLongLat.latitude))[0]));
                a = e.degreesToRadians(a / d * (k - g) + g);
                b = e.degreesToRadians(b / f * (m - l) + l);
                b = h(a, b);
                h = e.radiansToDegrees(b[0]);
                b = e.radiansToDegrees(b[1])
            } else h = this.coordinateToLongitude(a), b = this.coordinateToLatitude(b);
            return{longitude: e.roundTo(h,
                4), latitude: e.roundTo(b, 4)}
        }, coordinateToLatitude: function (a, b) {
            var c;
            void 0 === b && (b = this.mapHeight);
            if (this.mapSet) {
                var d = this.dataProvider, f = d.bottomLatitude;
                c = d.topLatitude;
                "mercator" == d.projection ? (d = this.mercatorLatitudeToCoordinate(f), c = this.mercatorLatitudeToCoordinate(c), c = 2 * e.degreesToRadians(a * (d - c) / b + c), c = e.radiansToDegrees(2 * Math.atan(Math.exp(c)) - .5 * Math.PI)) : c = a / b * (f - c) + c
            }
            return Math.round(1E6 * c) / 1E6
        }, coordinateToLongitude: function (a, b) {
            var c, d = this.dataProvider;
            void 0 === b && (b = this.mapWidth);
            this.mapSet && (c = a / b * (d.rightLongitude - d.leftLongitude) + d.leftLongitude);
            return Math.round(1E6 * c) / 1E6
        }})
})();
(function () {
    var e = window.AmCharts;
    e.ZoomControl = e.Class({construct: function (a) {
        this.cname = "ZoomControl";
        this.panStepSize = .1;
        this.zoomFactor = 2;
        this.maxZoomLevel = 64;
        this.minZoomLevel = 1;
        this.panControlEnabled = !1;
        this.zoomControlEnabled = !0;
        this.buttonRollOverColor = "#DADADA";
        this.buttonFillColor = "#FFFFFF";
        this.buttonFillAlpha = 1;
        this.buttonBorderColor = "#000000";
        this.buttonBorderAlpha = .1;
        this.buttonIconAlpha = this.buttonBorderThickness = 1;
        this.gridColor = this.buttonIconColor = "#000000";
        this.homeIconFile = "homeIcon.gif";
        this.gridBackgroundColor = "#000000";
        this.draggerAlpha = this.gridAlpha = this.gridBackgroundAlpha = 0;
        this.draggerSize = this.buttonSize = 31;
        this.iconSize = 11;
        this.homeButtonEnabled = !0;
        this.buttonCornerRadius = 2;
        this.gridHeight = 5;
        this.roundButtons = !0;
        this.top = this.left = 10;
        e.applyTheme(this, a, this.cname)
    }, init: function (a, b) {
        var c = this;
        c.chart = a;
        e.remove(c.set);
        var d = b.set();
        e.setCN(a, d, "zoom-control");
        var f = c.buttonSize, h = c.zoomControlEnabled, g = c.panControlEnabled, k = c.buttonFillColor, l = c.buttonFillAlpha, m = c.buttonBorderThickness,
            n = c.buttonBorderColor, p = c.buttonBorderAlpha, r = c.buttonCornerRadius, t = c.buttonRollOverColor, q = c.gridHeight, u = c.zoomFactor, C = c.minZoomLevel, v = c.maxZoomLevel, D = c.buttonIconAlpha, y = c.buttonIconColor, E = c.roundButtons, z = a.svgIcons, w = a.getX(c.left), x = a.getY(c.top);
        isNaN(c.right) || (w = a.getX(c.right, !0), w = g ? w - 3 * f : w - f);
        isNaN(c.bottom) || (x = a.getY(c.bottom, !0), h && (x -= q + 3 * f), x = g ? x - 3 * f : c.homeButtonEnabled ? x - .5 * f : x + f);
        d.translate(w, x);
        c.previousDY = NaN;
        var F, w = f / 4 - 1;
        if (h) {
            F = b.set();
            e.setCN(a, F, "zoom-control-zoom");
            d.push(F);
            c.set = d;
            c.zoomSet = F;
            5 < q && (h = e.rect(b, f + 6, q + 2 * f + 6, c.gridBackgroundColor, c.gridBackgroundAlpha, 0, "#000000", 0, 4), e.setCN(a, h, "zoom-bg"), h.translate(-3, -3), h.mouseup(function () {
                c.handleBgUp()
            }).touchend(function () {
                c.handleBgUp()
            }), F.push(h));
            var B = f;
            E && (B = f / 1.5);
            c.draggerSize = B;
            var G = Math.log(v / C) / Math.log(u) + 1;
            1E3 < G && (G = 1E3);
            var h = q / G, H, A = b.set();
            A.translate((f - B) / 2 + 1, 1, NaN, !0);
            F.push(A);
            for (H = 1; H < G; H++)x = f + H * h, x = e.line(b, [1, B - 2], [x, x], c.gridColor, c.gridAlpha, 1), e.setCN(a, x, "zoom-grid"), A.push(x);
            x = new e.SimpleButton;
            x.setDownHandler(c.draggerDown, c);
            x.setClickHandler(c.draggerUp, c);
            x.init(b, B, h, k, l, m, n, p, r, t);
            e.setCN(a, x.set, "zoom-dragger");
            F.push(x.set);
            x.set.setAttr("opacity", c.draggerAlpha);
            c.dragger = x.set;
            c.previousY = NaN;
            x = new e.SimpleButton;
            z ? (B = b.set(), G = e.line(b, [-w, w], [0, 0], y, D, 1), H = e.line(b, [0, 0], [-w, w], y, D, 1), B.push(G), B.push(H), x.svgIcon = B) : x.setIcon(a.pathToImages + "plus.gif", c.iconSize);
            x.setClickHandler(a.zoomIn, a);
            x.init(b, f, f, k, l, m, n, p, r, t, D, y, E);
            e.setCN(a, x.set, "zoom-in");
            F.push(x.set);
            x = new e.SimpleButton;
            z ? x.svgIcon = e.line(b, [-w, w], [0, 0], y, D, 1) : x.setIcon(a.pathToImages + "minus.gif", c.iconSize);
            x.setClickHandler(a.zoomOut, a);
            x.init(b, f, f, k, l, m, n, p, r, t, D, y, E);
            x.set.translate(0, q + f);
            e.setCN(a, x.set, "zoom-out");
            F.push(x.set);
            q -= h;
            v = Math.log(v / 100) / Math.log(u);
            c.realStepSize = q / (v - Math.log(C / 100) / Math.log(u));
            c.realGridHeight = q;
            c.stepMax = v
        }
        g && (g = b.set(), e.setCN(a, g, "zoom-control-pan"), d.push(g), F && F.translate(f, 4 * f), u = new e.SimpleButton, z ? u.svgIcon = e.line(b, [w / 5, -w + w / 5,
            w / 5], [-w, 0, w], y, D, 1) : u.setIcon(a.pathToImages + "panLeft.gif", c.iconSize), u.setClickHandler(a.moveLeft, a), u.init(b, f, f, k, l, m, n, p, r, t, D, y, E), u.set.translate(0, f), e.setCN(a, u.set, "pan-left"), g.push(u.set), u = new e.SimpleButton, z ? u.svgIcon = e.line(b, [-w / 5, w - w / 5, -w / 5], [-w, 0, w], y, D, 1) : u.setIcon(a.pathToImages + "panRight.gif", c.iconSize), u.setClickHandler(a.moveRight, a), u.init(b, f, f, k, l, m, n, p, r, t, D, y, E), u.set.translate(2 * f, f), e.setCN(a, u.set, "pan-right"), g.push(u.set), u = new e.SimpleButton, z ? u.svgIcon = e.line(b,
            [-w, 0, w], [w / 5, -w + w / 5, w / 5], y, D, 1) : u.setIcon(a.pathToImages + "panUp.gif", c.iconSize), u.setClickHandler(a.moveUp, a), u.init(b, f, f, k, l, m, n, p, r, t, D, y, E), u.set.translate(f, 0), e.setCN(a, u.set, "pan-up"), g.push(u.set), u = new e.SimpleButton, z ? u.svgIcon = e.line(b, [-w, 0, w], [-w / 5, w - w / 5, -w / 5], y, D, 1) : u.setIcon(a.pathToImages + "panDown.gif", c.iconSize), u.setClickHandler(a.moveDown, a), u.init(b, f, f, k, l, m, n, p, r, t, D, y, E), u.set.translate(f, 2 * f), e.setCN(a, u.set, "pan-down"), g.push(u.set), d.push(g));
        c.homeButtonEnabled && (g = new e.SimpleButton,
            z ? g.svgIcon = e.polygon(b, [-w, 0, w, w - 1, w - 1, 2, 2, -2, -2, -w + 1, -w + 1], [0, -w, 0, 0, w - 1, w - 1, 2, 2, w - 1, w - 1, 0], y, D, 1, y, D) : g.setIcon(a.pathToImages + c.homeIconFile, c.iconSize), g.setClickHandler(a.goHome, a), c.panControlEnabled && (p = l = 0), g.init(b, f, f, k, l, m, n, p, r, t, D, y, E), c.panControlEnabled ? g.set.translate(f, f) : F && F.translate(0, 1.5 * f), e.setCN(a, g.set, "pan-home"), d.push(g.set));
        c.update()
    }, draggerDown: function () {
        this.chart.stopDrag();
        this.isDragging = !0
    }, draggerUp: function () {
        this.isDragging = !1
    }, handleBgUp: function () {
        var a =
            this.chart;
        a.zoomTo(100 * Math.pow(this.zoomFactor, this.stepMax - (a.mouseY - this.zoomSet.y - this.set.y - this.buttonSize - this.realStepSize / 2) / this.realStepSize))
    }, update: function () {
        var a;
        a = this.zoomFactor;
        var b = this.realStepSize, c = this.stepMax, d = this.dragger, f = this.buttonSize, h, g = this.chart;
        g && (this.isDragging ? (g.stopDrag(), h = d.y + (g.mouseY - this.previousY), h = e.fitToBounds(h, f, this.realGridHeight + f), g.zoomTo(100 * Math.pow(a, c - (h - f) / b), NaN, NaN, !0)) : (a = Math.log(g.zoomLevel() / 100) / Math.log(a), h = (c - a) * b + f), this.previousY =
            g.mouseY, this.previousDY != h && d && (d.translate((this.buttonSize - this.draggerSize) / 2, h), this.previousDY = h))
    }})
})();
(function () {
    var e = window.AmCharts;
    e.SimpleButton = e.Class({construct: function () {
    }, init: function (a, b, c, d, f, h, g, k, l, m, n, p, r) {
        var t = this;
        t.rollOverColor = m;
        t.color = d;
        t.container = a;
        m = a.set();
        t.set = m;
        r ? (b /= 2, d = e.circle(a, b, d, f, h, g, k), d.translate(b, b)) : d = e.rect(a, b, c, d, f, h, g, k, l);
        m.push(d);
        f = t.iconPath;
        var q;
        f && (q = t.iconSize, h = (b - q) / 2, r && (h = (2 * b - q) / 2), q = a.image(f, h, (c - q) / 2, q, q));
        t.svgIcon && (q = t.svgIcon, r ? q.translate(b, b) : q.translate(b / 2, b / 2));
        m.setAttr("cursor", "pointer");
        q && (m.push(q), q.setAttr("opacity",
            n), q.node.style.pointerEvents = "none");
        d.mousedown(function () {
            t.handleDown()
        }).touchstart(function () {
            t.handleDown()
        }).mouseup(function () {
            t.handleUp()
        }).touchend(function () {
            t.handleUp()
        }).mouseover(function () {
            t.handleOver()
        }).mouseout(function () {
            t.handleOut()
        });
        t.bg = d
    }, setIcon: function (a, b) {
        this.iconPath = a;
        this.iconSize = b
    }, setClickHandler: function (a, b) {
        this.clickHandler = a;
        this.scope = b
    }, setDownHandler: function (a, b) {
        this.downHandler = a;
        this.scope = b
    }, handleUp: function () {
        var a = this.clickHandler;
        a && a.call(this.scope)
    },
        handleDown: function () {
            var a = this.downHandler;
            a && a.call(this.scope)
        }, handleOver: function () {
            this.container.chart.skipClick = !0;
            this.bg.setAttr("fill", this.rollOverColor)
        }, handleOut: function () {
            this.container.chart.skipClick = !1;
            this.bg.setAttr("fill", this.color)
        }})
})();
(function () {
    var e = window.AmCharts;
    e.SmallMap = e.Class({construct: function (a) {
        this.cname = "SmallMap";
        this.mapColor = "#e6e6e6";
        this.rectangleColor = "#FFFFFF";
        this.top = this.right = 10;
        this.minimizeButtonWidth = 23;
        this.backgroundColor = "#9A9A9A";
        this.backgroundAlpha = 1;
        this.borderColor = "#FFFFFF";
        this.iconColor = "#000000";
        this.borderThickness = 3;
        this.borderAlpha = 1;
        this.size = .2;
        this.enabled = !0;
        e.applyTheme(this, a, this.cname)
    }, init: function (a, b) {
        var c = this;
        if (c.enabled) {
            c.chart = a;
            c.container = b;
            c.width = a.realWidth *
                c.size;
            c.height = a.realHeight * c.size;
            e.remove(c.mapSet);
            e.remove(c.allSet);
            e.remove(c.set);
            var d = b.set();
            c.set = d;
            e.setCN(a, d, "small-map");
            var f = b.set();
            c.allSet = f;
            d.push(f);
            c.buildSVGMap();
            var h = c.borderThickness, g = c.borderColor, k = e.rect(b, c.width + h, c.height + h, c.backgroundColor, c.backgroundAlpha, h, g, c.borderAlpha);
            e.setCN(a, k, "small-map-bg");
            k.translate(-h / 2, -h / 2);
            f.push(k);
            k.toBack();
            var l, m, k = c.minimizeButtonWidth, n = new e.SimpleButton, p = k / 2;
            a.svgIcons ? n.svgIcon = e.line(b, [-p / 2, 0, p / 2], [-p / 4, p / 4, -p /
                4], c.iconColor, 1, 1) : n.setIcon(a.pathToImages + "arrowDown.gif", k);
            n.setClickHandler(c.minimize, c);
            n.init(b, k, k, g, 1, 1, g, 1);
            e.setCN(a, n.set, "small-map-down");
            n = n.set;
            c.downButtonSet = n;
            d.push(n);
            var r = new e.SimpleButton;
            a.svgIcons ? r.svgIcon = e.line(b, [-p / 2, 0, p / 2], [p / 4, -p / 4, p / 4], c.iconColor, 1, 1) : r.setIcon(a.pathToImages + "arrowUp.gif", k);
            r.setClickHandler(c.maximize, c);
            r.init(b, k, k, g, 1, 1, g, 1);
            e.setCN(a, r.set, "small-map-up");
            g = r.set;
            c.upButtonSet = g;
            g.hide();
            d.push(g);
            var t, q;
            isNaN(c.top) || (l = a.getY(c.top) +
                h, q = 0);
            isNaN(c.bottom) || (l = a.getY(c.bottom, !0) - c.height - h, q = c.height - k + h / 2);
            isNaN(c.left) || (m = a.getX(c.left) + h, t = -h / 2);
            isNaN(c.right) || (m = a.getX(c.right, !0) - c.width - h, t = c.width - k + h / 2);
            h = b.set();
            h.clipRect(1, 1, c.width, c.height);
            f.push(h);
            c.rectangleC = h;
            d.translate(m, l);
            n.translate(t, q);
            g.translate(t, q);
            f.mouseup(function () {
                c.handleMouseUp()
            });
            c.drawRectangle()
        } else e.remove(c.allSet), e.remove(c.downButtonSet), e.remove(c.upButtonSet)
    }, minimize: function () {
        this.downButtonSet.hide();
        this.upButtonSet.show();
        this.allSet.hide()
    }, maximize: function () {
        this.downButtonSet.show();
        this.upButtonSet.hide();
        this.allSet.show()
    }, buildSVGMap: function () {
        var a = this.chart, b = {fill: this.mapColor, stroke: this.mapColor, "stroke-opacity": 1}, c = this.container, d = c.set();
        e.setCN(a, d, "small-map-image");
        var f;
        for (f = 0; f < a.svgAreas.length; f++) {
            var h = c.path(a.svgAreas[f].path).attr(b);
            d.push(h)
        }
        this.allSet.push(d);
        b = d.getBBox();
        c = this.size * a.mapScale;
        f = -b.x * c;
        var h = -b.y * c, g = 0, k = 0;
        a.centerMap && (g = (this.width - b.width * c) / 2, k = (this.height -
            b.height * c) / 2);
        this.mapWidth = b.width * c;
        this.mapHeight = b.height * c;
        f += g;
        h += k;
        this.dx = g;
        this.dy = k;
        d.translate(f, h, c);
        this.mapSet = d;
        this.mapX = f;
        this.mapY = h
    }, update: function () {
        var a = this.chart;
        if (a) {
            var b = a.zoomLevel(), c = this.width, d = this.height, e = c / (a.realWidth * b), h = a.mapContainer.getBBox(), c = c / b, d = d / b, g = this.rectangle;
            g.translate(-(a.mapContainer.x + h.x * b) * e + this.dx, -(a.mapContainer.y + h.y * b) * e + this.dy);
            0 < c && 0 < d && (g.setAttr("width", Math.ceil(c + 1)), g.setAttr("height", Math.ceil(d + 1)));
            this.rWidth = c;
            this.rHeight =
                d
        }
    }, drawRectangle: function () {
        var a = this.rectangle;
        e.remove(a);
        a = e.rect(this.container, 10, 10, "#000", 0, 1, this.rectangleColor, 1);
        e.setCN(this.chart, a, "small-map-rectangle");
        this.rectangleC.push(a);
        this.rectangle = a
    }, handleMouseUp: function () {
        var a = this.chart, b = a.zoomLevel();
        a.zoomToMapXY(b, (a.mouseX - this.set.x - this.mapX) / this.size + a.diffX * a.mapScale, (a.mouseY - this.set.y - this.mapY) / this.size + a.diffY * a.mapScale)
    }})
})();
(function () {
    var e = window.AmCharts;
    e.AreasProcessor = e.Class({construct: function (a) {
        this.chart = a
    }, process: function (a) {
        this.updateAllAreas();
        this.allObjects = [];
        a = a.areas;
        var b = this.chart;
        b.outlines = [];
        var c = a.length, d, e, h = 0, g = !1, k = !1, l = 0;
        for (d = 0; d < c; d++)if (e = a[d], e = e.value, !isNaN(e)) {
            if (!1 === g || g < e)g = e;
            if (!1 === k || k > e)k = e;
            h += Math.abs(e);
            l++
        }
        this.minValue = k;
        this.maxValue = g;
        isNaN(b.minValue) || (this.minValue = b.minValue);
        isNaN(b.maxValue) || (this.maxValue = b.maxValue);
        b.maxValueReal = g;
        b.minValueReal = k;
        for (d =
                 0; d < c; d++)e = a[d], isNaN(e.value) ? e.percents = void 0 : (e.percents = (e.value - k) / h * 100, k == g && (e.percents = 100));
        for (d = 0; d < c; d++)e = a[d], this.createArea(e);
        b.outlinesToFront()
    }, updateAllAreas: function () {
        var a = this.chart, b = a.areasSettings, c = b.unlistedAreasColor, d = b.unlistedAreasAlpha, f = b.unlistedAreasOutlineColor, h = b.unlistedAreasOutlineAlpha, g = a.svgAreas, k = a.dataProvider, l = k.areas, m = {}, n;
        for (n = 0; n < l.length; n++)m[l[n].id] = l[n];
        for (n = 0; n < g.length; n++) {
            l = g[n];
            if (b.preserveOriginalAttributes) {
                if (l.customAttr)for (var p in l.customAttr)l.setAttr(p,
                    l.customAttr[p])
            } else {
                void 0 != c && l.setAttr("fill", c);
                isNaN(d) || l.setAttr("fill-opacity", d);
                void 0 != f && l.setAttr("stroke", f);
                isNaN(h) || l.setAttr("stroke-opacity", h);
                var r = b.outlineThickness;
                b.adjustOutlineThickness && (r = r / a.zoomLevel() / a.mapScale);
                l.setAttr("stroke-width", r)
            }
            e.setCN(a, l, "map-area-unlisted");
            k.getAreasFromMap && !m[l.id] && (r = new e.MapArea(a.theme), r.parentObject = k, r.id = l.id, r.outline = l.outline, k.areas.push(r))
        }
    }, createArea: function (a) {
        var b = this.chart, c = b.svgAreasById[a.id], d = b.areasSettings;
        if (c && c.className) {
            var f = b.areasClasses[c.className];
            f && (d = e.processObject(f, e.AreasSettings, b.theme))
        }
        var h = d.color, g = d.alpha, k = d.outlineThickness, l = d.rollOverColor, m = d.selectedColor, n = d.rollOverAlpha, p = d.rollOverBrightness, r = d.outlineColor, t = d.outlineAlpha, q = d.balloonText, u = d.selectable, C = d.pattern, v = d.rollOverOutlineColor, D = d.bringForwardOnHover, y = d.preserveOriginalAttributes;
        this.allObjects.push(a);
        a.chart = b;
        a.baseSettings = d;
        a.autoZoomReal = void 0 == a.autoZoom ? d.autoZoom : a.autoZoom;
        f = a.color;
        void 0 ==
            f && (f = h);
        var E = a.alpha;
        isNaN(E) && (E = g);
        g = a.rollOverAlpha;
        isNaN(g) && (g = n);
        isNaN(g) && (g = E);
        n = a.rollOverColor;
        void 0 == n && (n = l);
        l = a.pattern;
        void 0 == l && (l = C);
        C = a.selectedColor;
        void 0 == C && (C = m);
        m = a.balloonText;
        void 0 === m && (m = q);
        void 0 == d.colorSolid || isNaN(a.value) || (q = Math.floor((a.value - this.minValue) / ((this.maxValue - this.minValue) / b.colorSteps)), q == b.colorSteps && q--, q *= 1 / (b.colorSteps - 1), this.maxValue == this.minValue && (q = 1), a.colorReal = e.getColorFade(f, d.colorSolid, q));
        void 0 != a.color && (a.colorReal = a.color);
        void 0 == a.selectable && (a.selectable = u);
        void 0 == a.colorReal && (a.colorReal = h);
        h = a.outlineColor;
        void 0 == h && (h = r);
        r = a.outlineAlpha;
        isNaN(r) && (r = t);
        t = a.outlineThickness;
        isNaN(t) && (t = k);
        k = a.rollOverOutlineColor;
        void 0 == k && (k = v);
        v = a.rollOverBrightness;
        void 0 == v && (v = p);
        void 0 == a.bringForwardOnHover && (a.bringForwardOnHover = D);
        void 0 == a.preserveOriginalAttributes && (a.preserveOriginalAttributes = y);
        isNaN(d.selectedBrightness) || (C = e.adjustLuminosity(a.colorReal, d.selectedBrightness / 100));
        a.alphaReal = E;
        a.rollOverColorReal =
            n;
        a.rollOverAlphaReal = g;
        a.balloonTextReal = m;
        a.selectedColorReal = C;
        a.outlineColorReal = h;
        a.outlineAlphaReal = r;
        a.rollOverOutlineColorReal = k;
        a.outlineThicknessReal = t;
        a.patternReal = l;
        a.rollOverBrightnessReal = v;
        a.accessibleLabel || (a.accessibleLabel = d.accessibleLabel);
        e.processDescriptionWindow(d, a);
        if (c && (p = c.area, D = c.title, a.enTitle = c.title, D && !a.title && (a.title = D), (c = b.language) ? (D = e.mapTranslations) && (c = D[c]) && c[a.enTitle] && (a.titleTr = c[a.enTitle]) : a.titleTr = void 0, p)) {
            c = a.tabIndex;
            void 0 === c && (c = d.tabIndex);
            void 0 !== c && p.setAttr("tabindex", c);
            a.displayObject = p;
            a.outline && (E = 0, a.alphaReal = 0, a.rollOverAlphaReal = 0, a.mouseEnabled = !1, b.outlines.push(p), p.node.setAttribute("pointer-events", "none"));
            a.mouseEnabled && b.addObjectEventListeners(p, a);
            var z;
            void 0 != f && (z = f);
            void 0 != a.colorReal && (z = a.showAsSelected || b.selectedObject == a ? a.selectedColorReal : a.colorReal);
            p.node.setAttribute("class", "");
            e.setCN(b, p, "map-area");
            e.setCN(b, p, "map-area-" + p.id);
            d.adjustOutlineThickness && (t = t / b.zoomLevel() / b.mapScale);
            a.preserveOriginalAttributes ||
            (p.setAttr("fill", z), p.setAttr("stroke", h), p.setAttr("stroke-opacity", r), p.setAttr("stroke-width", t), p.setAttr("fill-opacity", E));
            b.makeObjectAccessible(a);
            l && p.pattern(l, b.mapScale, b.path);
            a.hidden && p.hide()
        }
    }})
})();
(function () {
    var e = window.AmCharts;
    e.AreasSettings = e.Class({construct: function (a) {
        this.cname = "AreasSettings";
        this.alpha = 1;
        this.autoZoom = !1;
        this.balloonText = "[[title]]";
        this.color = "#FFCC00";
        this.colorSolid = "#990000";
        this.unlistedAreasAlpha = 1;
        this.unlistedAreasColor = "#DDDDDD";
        this.outlineColor = "#FFFFFF";
        this.outlineThickness = this.outlineAlpha = 1;
        this.selectedColor = this.rollOverOutlineColor = "#CC0000";
        this.unlistedAreasOutlineColor = "#FFFFFF";
        this.unlistedAreasOutlineAlpha = 1;
        this.descriptionWindowWidth =
            250;
        this.bringForwardOnHover = this.adjustOutlineThickness = !0;
        this.accessibleLabel = "[[title]] [[value]] [[description]]";
        e.applyTheme(this, a, this.cname)
    }})
})();
(function () {
    var e = window.AmCharts;
    e.ImagesProcessor = e.Class({construct: function (a) {
        this.chart = a;
        this.reset()
    }, process: function (a) {
        var b = a.images, c;
        for (c = 0; c < b.length; c++) {
            var d = b[c];
            this.createImage(d, c);
            d.parentArray = b
        }
        this.counter = c;
        a.parentObject && a.remainVisible && this.process(a.parentObject)
    }, createImage: function (a, b) {
        a = e.processObject(a, e.MapImage);
        a.arrays = [];
        isNaN(b) && (this.counter++, b = this.counter);
        var c = this.chart, d = c.container, f = c.mapImagesContainer, h = c.stageImagesContainer, g = c.imagesSettings;
        a.remove && a.remove();
        var k = g.color, l = g.alpha, m = g.rollOverColor, n = g.rollOverOutlineColor, p = g.selectedColor, r = g.balloonText, t = g.outlineColor, q = g.outlineAlpha, u = g.outlineThickness, C = g.selectedScale, v = g.rollOverScale, D = g.selectable, y = g.labelPosition, E = g.labelColor, z = g.labelFontSize, w = g.bringForwardOnHover, x = g.labelRollOverColor, F = g.rollOverBrightness, B = g.selectedLabelColor;
        a.index = b;
        a.chart = c;
        a.baseSettings = c.imagesSettings;
        var G = d.set();
        a.displayObject = G;
        var H = a.color;
        void 0 == H && (H = k);
        k = a.alpha;
        isNaN(k) &&
        (k = l);
        void 0 == a.bringForwardOnHover && (a.bringForwardOnHover = w);
        l = a.outlineAlpha;
        isNaN(l) && (l = q);
        q = a.rollOverColor;
        void 0 == q && (q = m);
        m = a.selectedColor;
        void 0 == m && (m = p);
        p = a.balloonText;
        void 0 === p && (p = r);
        r = a.outlineColor;
        void 0 == r && (r = t);
        a.outlineColorReal = r;
        t = a.outlineThickness;
        isNaN(t) && (t = u);
        (u = a.labelPosition) || (u = y);
        y = a.labelColor;
        void 0 == y && (y = E);
        E = a.labelRollOverColor;
        void 0 == E && (E = x);
        x = a.selectedLabelColor;
        void 0 == x && (x = B);
        B = a.labelFontSize;
        isNaN(B) && (B = z);
        z = a.selectedScale;
        isNaN(z) && (z = C);
        C =
            a.rollOverScale;
        isNaN(C) && (C = v);
        v = a.rollOverBrightness;
        void 0 == v && (v = F);
        void 0 == a.selectable && (a.selectable = D);
        a.colorReal = H;
        isNaN(g.selectedBrightness) || (m = e.adjustLuminosity(a.colorReal, g.selectedBrightness / 100));
        a.alphaReal = k;
        a.rollOverColorReal = q;
        a.balloonTextReal = p;
        a.selectedColorReal = m;
        a.labelColorReal = y;
        a.labelRollOverColorReal = E;
        a.selectedLabelColorReal = x;
        a.labelFontSizeReal = B;
        a.labelPositionReal = u;
        a.selectedScaleReal = z;
        a.rollOverScaleReal = C;
        a.rollOverOutlineColorReal = n;
        a.rollOverBrightnessReal =
            v;
        a.accessibleLabel || (a.accessibleLabel = g.accessibleLabel);
        e.processDescriptionWindow(g, a);
        a.centeredReal = void 0 == a.centered ? g.centered : a.centered;
        n = a.type;
        v = a.imageURL;
        C = a.svgPath;
        z = a.width;
        B = a.height;
        D = a.scale;
        isNaN(a.percentWidth) || (z = a.percentWidth / 100 * c.realWidth);
        isNaN(a.percentHeight) || (B = a.percentHeight / 100 * c.realHeight);
        var A;
        v || n || C || (n = "circle", z = 1, l = k = 0);
        q = F = 0;
        g = a.selectedColorReal;
        if (n) {
            isNaN(z) && (z = 10);
            isNaN(B) && (B = 10);
            "kilometers" == a.widthAndHeightUnits && (z = c.kilometersToPixels(a.width),
                B = c.kilometersToPixels(a.height));
            "miles" == a.widthAndHeightUnits && (z = c.milesToPixels(a.width), B = c.milesToPixels(a.height));
            if ("circle" == n || "bubble" == n)B = z;
            A = this.createPredefinedImage(H, r, t, n, z, B);
            q = F = 0;
            a.centeredReal ? (isNaN(a.right) || (F = z * D), isNaN(a.bottom) || (q = B * D)) : (F = z * D / 2, q = B * D / 2);
            A.translate(F, q, D, !0)
        } else v ? (isNaN(z) && (z = 10), isNaN(B) && (B = 10), A = d.image(v, 0, 0, z, B), A.node.setAttribute("preserveAspectRatio", "none"), A.setAttr("opacity", k), a.centeredReal && (F = isNaN(a.right) ? -z / 2 : z / 2, q = isNaN(a.bottom) ?
            -B / 2 : B / 2, A.translate(F, q, NaN, !0))) : C && (A = d.path(C), v = A.getBBox(), a.centeredReal ? (F = -v.x * D - v.width * D / 2, isNaN(a.right) || (F = -F), q = -v.y * D - v.height * D / 2, isNaN(a.bottom) || (q = -q)) : F = q = 0, A.translate(F, q, D, !0), A.x = F, A.y = q);
        A && (G.push(A), a.image = A, A.setAttr("stroke-opacity", l), A.setAttr("stroke-width", t), A.setAttr("stroke", r), A.setAttr("fill-opacity", k), "bubble" != n && A.setAttr("fill", H), e.setCN(c, A, "map-image"), void 0 != a.id && e.setCN(c, A, "map-image-" + a.id));
        H = a.labelColorReal;
        !a.showAsSelected && c.selectedObject !=
            a || void 0 == g || (A.setAttr("fill", g), H = a.selectedLabelColorReal);
        A = null;
        void 0 !== a.label && (A = e.text(d, a.label, H, c.fontFamily, a.labelFontSizeReal, a.labelAlign), e.setCN(c, A, "map-image-label"), void 0 !== a.id && e.setCN(c, A, "map-image-label-" + a.id), H = a.labelBackgroundAlpha, (k = a.labelBackgroundColor) && 0 < H && (l = A.getBBox(), d = e.rect(d, l.width + 16, l.height + 10, k, H), e.setCN(c, d, "map-image-label-background"), void 0 != a.id && e.setCN(c, d, "map-image-label-background-" + a.id), G.push(d), a.labelBG = d), a.imageLabel = A, G.push(A),
            e.setCN(c, G, "map-image-container"), void 0 != a.id && e.setCN(c, G, "map-image-container-" + a.id), this.labelsToReposition.push(a), a.arrays.push({arr: this.labelsToReposition, el: a}));
        d = isNaN(a.latitude) || isNaN(a.longitude) ? !0 : !1;
        a.lineId && (A = this.chart.getObjectById(a.lineId)) && 0 < A.longitudes.length && (d = !1);
        d ? h.push(G) : f.push(G);
        G && (G.rotation = a.rotation, isNaN(a.rotation) || G.rotate(a.rotation), a.arrays.push({arr: this.allSvgObjects, el: G}), this.allSvgObjects.push(G));
        this.allObjects.push(a);
        c.makeObjectAccessible(a);
        f = a.tabIndex;
        void 0 === f && (f = c.imagesSettings.tabIndex);
        void 0 !== f && G.setAttr("tabindex", f);
        a.arrays.push({arr: this.allObjects, el: a});
        isNaN(a.longitude) || isNaN(a.latitude) || !a.fixedSize || (a.objToResize = {image: G, mapImage: a, scale: 1}, this.objectsToResize.push(a.objToResize), a.arrays.push({arr: this.objectsToResize, el: a.objToResize}));
        this.updateSizeAndPosition(a);
        a.mouseEnabled && c.addObjectEventListeners(G, a);
        a.hidden && G.hide();
        e.removeFromArray(c.updatableImages, a);
        a.animateAlongLine && (c.updatableImages.push(a),
            a.delayAnimateAlong());
        return a
    }, updateSizeAndPosition: function (a) {
        var b = this.chart, c = a.displayObject, d = b.getX(a.left), f = b.getY(a.top), h, g = a.image.getBBox();
        isNaN(a.right) || (d = b.getX(a.right, !0) - g.width * a.scale);
        isNaN(a.bottom) || (f = b.getY(a.bottom, !0) - g.height * a.scale);
        var k = a.longitude, l = a.latitude, m = a.positionOnLine, g = a.imageLabel, n = this.chart.zoomLevel(), p, r;
        a.lineId && (a.line = this.chart.getObjectById(a.lineId));
        if (a.line && a.line.getCoordinates) {
            a.line.chart = b;
            var t = a.line.getCoordinates(m, a.lineSegment);
            t && (k = b.coordinateToLongitude(t.x), l = b.coordinateToLatitude(t.y), p = t.x, r = t.y, a.animateAngle && (h = e.radiansToDegrees(t.angle)))
        }
        isNaN(h) || c.rotate(h + a.extraAngle);
        if (!isNaN(d) && !isNaN(f))c.translate(d, f, NaN, !0); else if (!isNaN(l) && !isNaN(k))if (f = b.coordinatesToXY(k, l), d = f.x, f = f.y, isNaN(p) || (d = p), isNaN(r) || (f = r), a.fixedSize) {
            p = a.positionScale;
            isNaN(p) ? p = 0 : (--p, p *= 1 - 2 * Math.abs(m - .5));
            if (m = a.objectToResize)m.scale = 1 + p;
            c.translate(d, f, 1 / n + p, !0)
        } else c.translate(d, f, NaN, !0);
        this.positionLabel(g, a, a.labelPositionReal)
    },
        positionLabel: function (a, b, c) {
            if (a) {
                var d = b.image, e = 0, h = 0, g = 0, k = 0;
                d && (k = d.getBBox(), h = d.y + k.y, e = d.x + k.x, g = k.width, k = k.height, b.svgPath && (g *= b.scale, k *= b.scale));
                var d = a.getBBox(), l = d.width, m = d.height;
                "right" == c && (e += g + l / 2 + 5, h += k / 2 - 2);
                "left" == c && (e += -l / 2 - 5, h += k / 2 - 2);
                "top" == c && (h -= m / 2 + 3, e += g / 2);
                "bottom" == c && (h += k + m / 2, e += g / 2);
                "middle" == c && (e += g / 2, h += k / 2);
                a.translate(e + b.labelShiftX, h + b.labelShiftY, NaN, !0);
                a = b.labelFontSizeReal;
                b.labelBG && b.labelBG.translate(e - d.width / 2 + b.labelShiftX - 9, h - a / 2 + b.labelShiftY -
                    4, NaN, !0)
            }
        }, createPredefinedImage: function (a, b, c, d, f, h) {
            var g = this.chart.container, k;
            switch (d) {
                case "circle":
                    k = e.circle(g, f / 2, a, 1, c, b, 1);
                    break;
                case "rectangle":
                    k = e.polygon(g, [-f / 2, f / 2, f / 2, -f / 2], [h / 2, h / 2, -h / 2, -h / 2], a, 1, c, b, 1, 0, !0);
                    break;
                case "bubble":
                    k = e.circle(g, f / 2, a, 1, c, b, 1, !0);
                    break;
                case "hexagon":
                    f /= Math.sqrt(3), k = e.polygon(g, [.866 * f, 0 * f, -.866 * f, -.866 * f, 0 * f, .866 * f], [.5 * f, 1 * f, .5 * f, -.5 * f, -1 * f, -.5 * f], a, 1, c, b, 1)
            }
            return k
        }, reset: function () {
            this.objectsToResize = [];
            this.allSvgObjects = [];
            this.allObjects =
                [];
            this.allLabels = [];
            this.labelsToReposition = []
        }})
})();
(function () {
    var e = window.AmCharts;
    e.ImagesSettings = e.Class({construct: function (a) {
        this.cname = "ImagesSettings";
        this.balloonText = "[[title]]";
        this.alpha = 1;
        this.borderAlpha = 0;
        this.borderThickness = 1;
        this.labelPosition = "right";
        this.labelColor = "#000000";
        this.labelFontSize = 11;
        this.color = "#000000";
        this.labelRollOverColor = "#00CC00";
        this.centered = !0;
        this.rollOverScale = this.selectedScale = 1;
        this.descriptionWindowWidth = 250;
        this.bringForwardOnHover = !0;
        this.outlineColor = "transparent";
        this.adjustAnimationSpeed = !1;
        this.baseAnimationDistance = 500;
        this.pauseDuration = 0;
        this.easingFunction = e.easeInOutQuad;
        this.animationDuration = 3;
        this.positionScale = 1;
        this.accessibleLabel = "[[title]] [[description]]";
        e.applyTheme(this, a, this.cname)
    }})
})();
(function () {
    var e = window.AmCharts;
    e.LinesProcessor = e.Class({construct: function (a) {
        this.chart = a;
        this.reset()
    }, process: function (a) {
        var b = a.lines, c;
        for (c = 0; c < b.length; c++) {
            var d = b[c];
            this.createLine(d, c);
            d.parentArray = b
        }
        this.counter = c;
        a.parentObject && a.remainVisible && this.process(a.parentObject)
    }, createLine: function (a, b) {
        a = e.processObject(a, e.MapLine);
        isNaN(b) && (this.counter++, b = this.counter);
        a.index = b;
        a.remove && a.remove();
        var c = this.chart, d = c.linesSettings, f = this.objectsToResize, h = c.mapLinesContainer,
            g = c.stageLinesContainer, k = d.thickness, l = d.dashLength, m = d.arrow, n = d.arrowSize, p = d.arrowColor, r = d.arrowAlpha, t = d.color, q = d.alpha, u = d.rollOverColor, C = d.selectedColor, v = d.rollOverAlpha, D = d.balloonText, y = d.bringForwardOnHover, E = d.arc, z = d.rollOverBrightness, w = c.container;
        a.chart = c;
        a.baseSettings = d;
        var x = w.set();
        a.displayObject = x;
        var F = a.tabIndex;
        void 0 === F && (F = d.tabIndex);
        void 0 !== F && x.setAttr("tabindex", F);
        this.allSvgObjects.push(x);
        a.arrays.push({arr: this.allSvgObjects, el: x});
        this.allObjects.push(a);
        a.arrays.push({arr: this.allObjects, el: a});
        a.mouseEnabled && c.addObjectEventListeners(x, a);
        if (a.remainVisible || c.selectedObject == a.parentObject) {
            F = a.thickness;
            isNaN(F) && (F = k);
            k = a.dashLength;
            isNaN(k) && (k = l);
            l = a.color;
            void 0 == l && (l = t);
            t = a.alpha;
            isNaN(t) && (t = q);
            q = a.rollOverAlpha;
            isNaN(q) && (q = v);
            isNaN(q) && (q = t);
            v = a.rollOverColor;
            void 0 == v && (v = u);
            u = a.selectedColor;
            void 0 == u && (u = C);
            C = a.balloonText;
            void 0 === C && (C = D);
            D = a.arc;
            isNaN(D) && (D = E);
            E = a.arrow;
            if (!E || "none" == E && "none" != m)E = m;
            m = a.arrowColor;
            void 0 == m &&
            (m = p);
            void 0 == m && (m = l);
            p = a.arrowAlpha;
            isNaN(p) && (p = r);
            isNaN(p) && (p = t);
            r = a.arrowSize;
            isNaN(r) && (r = n);
            n = a.rollOverBrightness;
            void 0 == n && (n = z);
            a.colorReal = l;
            a.arrowColor = m;
            isNaN(d.selectedBrightness) || (u = e.adjustLuminosity(a.colorReal, d.selectedBrightness / 100));
            a.alphaReal = t;
            a.rollOverColorReal = v;
            a.rollOverAlphaReal = q;
            a.balloonTextReal = C;
            a.selectedColorReal = u;
            a.thicknessReal = F;
            a.rollOverBrightnessReal = n;
            a.accessibleLabel || (a.accessibleLabel = d.accessibleLabel);
            void 0 === a.shiftArrow && (a.shiftArrow = d.shiftArrow);
            void 0 == a.bringForwardOnHover && (a.bringForwardOnHover = y);
            e.processDescriptionWindow(d, a);
            y = this.processCoordinates(a.x, c.realWidth);
            z = this.processCoordinates(a.y, c.realHeight);
            n = a.longitudes;
            d = a.latitudes;
            q = n.length;
            if (0 < q)for (y = [], z = [], v = 0; v < q; v++)C = c.coordinatesToXY(n[v], d[v]), y.push(C.x), z.push(C.y);
            if (0 < y.length) {
                a.segments = y.length;
                e.dx = 0;
                e.dy = 0;
                var B, G, H, q = 10 * (1 - Math.abs(D));
                10 <= q && (q = NaN);
                1 > q && (q = 1);
                a.arcRadius = [];
                a.distances = [];
                n = c.mapContainer.scale;
                if (isNaN(q)) {
                    for (q = 0; q < y.length - 1; q++)G =
                        Math.sqrt(Math.pow(y[q + 1] - y[q], 2) + Math.pow(z[q + 1] - z[q], 2)), a.distances[q] = G;
                    q = e.line(w, y, z, l, 1, F / n, k, !1, !1, !0);
                    l = e.line(w, y, z, l, .001, 5 / n, k, !1, !1, !0);
                    q.setAttr("stroke-linecap", "round")
                } else {
                    v = 1;
                    0 > D && (v = 0);
                    C = {fill: "none", stroke: l, "stroke-opacity": 1, "stroke-width": F / n, "fill-opacity": 0, "stroke-linecap": "round"};
                    void 0 !== k && 0 < k && (C["stroke-dasharray"] = k);
                    for (var k = "", A = 0; A < y.length - 1; A++) {
                        var L = y[A], M = y[A + 1], N = z[A], O = z[A + 1];
                        G = Math.sqrt(Math.pow(M - L, 2) + Math.pow(O - N, 2));
                        H = G / 2 * q;
                        B = 270 + 180 * Math.acos(G / 2 /
                            H) / Math.PI;
                        isNaN(B) && (B = 270);
                        if (L < M) {
                            var P = L, L = M, M = P, P = N, N = O, O = P;
                            B = -B
                        }
                        0 < D && (B = -B);
                        k += "M" + L + "," + N + "A" + H + "," + H + ",0,0," + v + "," + M + "," + O;
                        a.arcRadius[A] = H;
                        a.distances[A] = G
                    }
                    q = w.path(k).attr(C);
                    l = w.path(k).attr({"fill-opacity": 0, stroke: l, "stroke-width": 5 / n, "stroke-opacity": .001, fill: "none"})
                }
                e.setCN(c, q, "map-line");
                void 0 != a.id && e.setCN(c, q, "map-line-" + a.id);
                e.dx = .5;
                e.dy = .5;
                x.push(q);
                x.push(l);
                q.setAttr("opacity", t);
                if ("none" != E) {
                    var I, J, K;
                    if ("end" == E || "both" == E)v = y[y.length - 1], A = z[z.length - 1], 1 < y.length ?
                        (C = y[y.length - 2], I = z[z.length - 2]) : (C = v, I = A), I = 180 * Math.atan((A - I) / (v - C)) / Math.PI, isNaN(B) || (I += B), J = v, K = A, I = 0 > v - C ? I - 90 : I + 90;
                    t = [-r / 2 - .5, -.5, r / 2 - .5];
                    k = [r, -.5, r];
                    a.shiftArrow && "middle" != E && (k = [0, 1.2 * -r, 0]);
                    "both" == E && (r = e.polygon(w, t, k, m, p, 1, m, p, void 0, !0), x.push(r), r.translate(J, K, 1 / n, !0), isNaN(I) || r.rotate(I), e.setCN(c, q, "map-line-arrow"), void 0 != a.id && e.setCN(c, q, "map-line-arrow-" + a.id), a.fixedSize && f.push(r));
                    if ("start" == E || "both" == E)r = y[0], K = z[0], 1 < y.length ? (v = y[1], J = z[1]) : (v = r, J = K), I = 180 * Math.atan((K -
                        J) / (r - v)) / Math.PI, isNaN(B) || (I -= B), J = r, I = 0 > r - v ? I - 90 : I + 90;
                    "middle" == E && (v = y[y.length - 1], A = z[z.length - 1], 1 < y.length ? (C = y[y.length - 2], I = z[z.length - 2]) : (C = v, I = A), J = C + (v - C) / 2, K = I + (A - I) / 2, I = 180 * Math.atan((A - I) / (v - C)) / Math.PI, isNaN(B) || (B = G / 2, H -= Math.sqrt(H * H - B * B), 0 > D && (H = -H), B = Math.sin(I / 180 * Math.PI), -1 == B && (B = 1), J -= B * H, K += Math.cos(I / 180 * Math.PI) * H), I = 0 > v - C ? I - 90 : I + 90);
                    r = e.polygon(w, t, k, m, p, 1, m, p, void 0, !0);
                    e.setCN(c, q, "map-line-arrow");
                    void 0 != a.id && e.setCN(c, q, "map-line-arrow-" + a.id);
                    x.push(r);
                    r.translate(J,
                        K, 1 / n, !0);
                    isNaN(I) || r.rotate(I);
                    a.fixedSize && (f.push(r), a.arrays.push({arr: f, el: r}));
                    a.arrowSvg = r
                }
                a.fixedSize && q && (f = {line: q, thickness: F}, this.linesToResize.push(f), a.arrays.push({arr: this.linesToResize, el: f}), f = {line: l, thickness: 5}, this.linesToResize.push(f), a.arrays.push({arr: this.linesToResize, el: f}));
                a.lineSvg = q;
                a.showAsSelected && !isNaN(u) && q.setAttr("stroke", u);
                0 < d.length ? h.push(x) : g.push(x);
                a.hidden && x.hide();
                c.makeObjectAccessible(a)
            }
        }
    }, processCoordinates: function (a, b) {
        var c = [], d;
        for (d = 0; d <
            a.length; d++) {
            var e = a[d], h = Number(e);
            isNaN(h) && (h = Number(e.replace("%", "")) * b / 100);
            isNaN(h) || c.push(h)
        }
        return c
    }, reset: function () {
        this.objectsToResize = [];
        this.allSvgObjects = [];
        this.allObjects = [];
        this.linesToResize = []
    }})
})();
(function () {
    var e = window.AmCharts;
    e.LinesSettings = e.Class({construct: function (a) {
        this.cname = "LinesSettings";
        this.balloonText = "[[title]]";
        this.thickness = 1;
        this.dashLength = 0;
        this.arrowSize = 10;
        this.arrowAlpha = 1;
        this.arrow = "none";
        this.color = "#990000";
        this.descriptionWindowWidth = 250;
        this.bringForwardOnHover = !0;
        e.applyTheme(this, a, this.cname)
    }})
})();
(function () {
    var e = window.AmCharts;
    e.MapObject = e.Class({construct: function (a) {
        this.fixedSize = this.mouseEnabled = !0;
        this.images = [];
        this.lines = [];
        this.areas = [];
        this.remainVisible = !0;
        this.passZoomValuesToTarget = !1;
        this.objectType = this.cname;
        e.applyTheme(this, a, "MapObject");
        this.arrays = []
    }, deleteObject: function () {
        this.remove();
        this.parentArray && e.removeFromArray(this.parentArray, this);
        if (this.arrays)for (var a = 0; a < this.arrays.length; a++)e.removeFromArray(this.arrays[a].arr, this.arrays[a].el);
        this.arrays =
            []
    }})
})();
(function () {
    var e = window.AmCharts;
    e.MapArea = e.Class({inherits: e.MapObject, construct: function (a) {
        this.cname = "MapArea";
        e.MapArea.base.construct.call(this, a);
        e.applyTheme(this, a, this.cname)
    }, validate: function () {
        this.chart.areasProcessor.createArea(this)
    }})
})();
(function () {
    var e = window.AmCharts;
    e.MapLine = e.Class({inherits: e.MapObject, construct: function (a) {
        this.cname = "MapLine";
        this.longitudes = [];
        this.latitudes = [];
        this.x = [];
        this.y = [];
        this.segments = 0;
        this.arrow = "none";
        e.MapLine.base.construct.call(this, a);
        e.applyTheme(this, a, this.cname)
    }, validate: function () {
        this.chart.linesProcessor.createLine(this)
    }, remove: function () {
        var a = this.displayObject;
        a && a.remove()
    }, getCoordinates: function (a, b) {
        isNaN(b) && (b = 0);
        isNaN(this.arc) || this.isValid || (this.isValid = !0, this.validate());
        if (!isNaN(a)) {
            var c, d, f, h, g, k;
            if (1 < this.longitudes.length) {
                d = this.chart.coordinatesToXY(this.longitudes[b], this.latitudes[b]);
                var l = this.chart.coordinatesToXY(this.longitudes[b + 1], this.latitudes[b + 1]);
                c = d.x;
                f = l.x;
                d = d.y;
                h = l.y
            } else 1 < this.x.length && (c = this.x[b], f = this.x[b + 1], d = this.y[b], h = this.y[b + 1]);
            l = Math.sqrt(Math.pow(f - c, 2) + Math.pow(h - d, 2));
            c < f && !isNaN(this.arc) && 0 !== this.arc && (a = 1 - a);
            g = c + (f - c) * a;
            k = d + (h - d) * a;
            var m = Math.atan2(h - d, f - c);
            if (!isNaN(this.arc) && 0 !== this.arc && this.arcRadius) {
                var n = 0;
                c <
                    f && (n = c, c = f, f = n, n = d, d = h, h = n, n = Math.PI);
                k = this.arcRadius[b];
                0 > this.arc && (l = -l);
                g = c + (f - c) / 2 + Math.sqrt(k * k - l / 2 * (l / 2)) * (d - h) / l;
                var p = d + (h - d) / 2 + Math.sqrt(k * k - l / 2 * (l / 2)) * (f - c) / l;
                c = 180 * Math.atan2(d - p, c - g) / Math.PI;
                f = 180 * Math.atan2(h - p, f - g) / Math.PI;
                180 < f - c && (f -= 360);
                m = e.degreesToRadians(c + (f - c) * a);
                g += k * Math.cos(m);
                k = p + k * Math.sin(m);
                m = 0 < this.arc ? m + Math.PI / 2 : m - Math.PI / 2;
                m += n
            }
            this.distance = l;
            return{x: g, y: k, angle: m}
        }
    }, fixToStage: function () {
        if (0 < this.latitudes.length) {
            this.y = [];
            for (var a = 0; a < this.latitudes.length; a++) {
                var b =
                    this.chart.coordinatesToStageXY(this.longitudes[a], this.latitudes[a]);
                this.y.push(b.y);
                this.x.push(b.x)
            }
            this.latitudes = [];
            this.longitudes = []
        }
        this.validate()
    }, fixToMap: function () {
        if (0 < this.y.length) {
            this.latitudes = [];
            for (var a = 0; a < this.y.length; a++) {
                var b = this.chart.stageXYToCoordinates(this.x[a], this.y[a]);
                this.latitudes.push(b.latitude);
                this.longitudes.push(b.longitude)
            }
            this.y = [];
            this.x = []
        }
        this.validate()
    }})
})();
(function () {
    var e = window.AmCharts;
    e.MapImage = e.Class({inherits: e.MapObject, construct: function (a) {
        this.cname = "MapImage";
        this.scale = 1;
        this.widthAndHeightUnits = "pixels";
        this.labelShiftY = this.labelShiftX = 0;
        this.positionOnLine = .5;
        this.direction = 1;
        this.lineSegment = this.extraAngle = 0;
        this.animateAngle = !0;
        this.createEvents("animationStart", "animationEnd");
        e.MapImage.base.construct.call(this, a);
        e.applyTheme(this, a, this.cname);
        this.delayCounter = 0
    }, validate: function () {
        this.chart.imagesProcessor.createImage(this)
    },
        updatePosition: function () {
            this.chart.imagesProcessor.updateSizeAndPosition(this)
        }, remove: function () {
            var a = this.displayObject;
            a && a.remove();
            (a = this.imageLabel) && a.remove()
        }, animateTo: function (a, b, c, d) {
            isNaN(c) || (this.animationDuration = c);
            d && (this.easingFunction = d);
            this.finalX = a;
            this.finalY = b;
            isNaN(this.longitude) || (this.initialX = this.longitude);
            isNaN(this.left) || (this.initialX = this.left);
            isNaN(this.right) || (this.initialX = this.right);
            isNaN(this.latitude) || (this.initialY = this.latitude);
            isNaN(this.top) ||
            (this.initialY = this.top);
            isNaN(this.bottom) || (this.initialY = this.bottom);
            this.animatingAlong = !1;
            this.animate()
        }, animateAlong: function (a, b, c) {
            1 == this.positionOnLine && this.flipDirection && (this.direction = -1, this.extraAngle = 180);
            isNaN(b) || (this.animationDuration = b);
            c && (this.easingFunction = c);
            a && (this.line = this.chart.getObjectById(a));
            this.animateAlongLine = this.line;
            this.animatingAlong = !0;
            this.animate()
        }, animate: function () {
            var a = this.chart.imagesSettings, b = this.animationDuration;
            isNaN(b) && (b = a.animationDuration);
            this.totalFrames = b * e.updateRate;
            b = 1;
            this.line && a.adjustAnimationSpeed && (this.line.distances && (b = this.line.distances[this.lineSegment] * this.chart.zoomLevel(), b = Math.abs(b / a.baseAnimationDistance)), this.totalFrames = Math.round(b * this.totalFrames));
            this.frame = 0;
            this.fire({type: "animationStart", chart: this.chart, image: this, lineSegment: this.lineSegment, direction: this.direction})
        }, update: function () {
            var a = this.totalFrames;
            this.frame++;
            this.delayCounter--;
            0 === this.delayCounter && this.animateAlong();
            if (!(0 < this.delayCounter))if (this.frame <=
                a) {
                this.updatePosition();
                var b = this.chart.imagesSettings, c = this.easingFunction;
                c || (c = b.easingFunction);
                a = c(0, this.frame, 0, 1, a);
                -1 == this.direction && (a = 1 - a);
                this.animatingAlong ? this.positionOnLine = a : (b = this.initialX + (this.finalX - this.initialX) * a, isNaN(this.longitude) || (this.longitude = b), isNaN(this.left) || (this.left = b), isNaN(this.right) || (this.right = b), a = this.initialY + (this.finalY - this.initialY) * a, isNaN(this.latitude) || (this.latitude = a), isNaN(this.top) || (this.top = a), isNaN(this.bottom) || (this.bottom =
                    a))
            } else this.frame == a + 1 && (this.fire({type: "animationEnd", chart: this.chart, image: this, lineSegment: this.lineSegment, direction: this.direction}), this.line && this.animatingAlong && (1 == this.direction ? this.lineSegment < this.line.segments - 2 ? (this.lineSegment++, this.delayAnimateAlong(), this.positionOnLine = 0) : this.flipDirection ? (this.direction = -1, this.extraAngle = 180, this.delayAnimateAlong()) : this.loop && (this.delayAnimateAlong(), this.lineSegment = 0) : 0 < this.lineSegment ? (this.lineSegment--, this.delayAnimateAlong(),
                this.positionOnLine = 0) : this.loop && this.flipDirection ? (this.direction = 1, this.extraAngle = 0, this.delayAnimateAlong()) : this.loop && this.delayAnimateAlong()))
        }, delayAnimateAlong: function () {
            this.animateAlongLine && (this.delayCounter = this.chart.imagesSettings.pauseDuration * e.updateRate)
        }, fixToStage: function () {
            if (!isNaN(this.longitude)) {
                var a = this.chart.coordinatesToStageXY(this.longitude, this.latitude);
                this.left = a.x;
                this.top = a.y;
                this.latitude = this.longitude = void 0
            }
            this.validate()
        }, fixToMap: function () {
            if (!isNaN(this.left)) {
                var a =
                    this.chart.stageXYToCoordinates(this.left, this.top);
                this.longitude = a.longitude;
                this.latitude = a.latitude;
                this.top = this.left = void 0
            }
            this.validate()
        }})
})();
(function () {
    var e = window.AmCharts;
    e.degreesToRadians = function (a) {
        return a / 180 * Math.PI
    };
    e.radiansToDegrees = function (a) {
        return a / Math.PI * 180
    };
    e.getColorFade = function (a, b, c) {
        var d = e.hex2RGB(b);
        b = d[0];
        var f = d[1], d = d[2], h = e.hex2RGB(a);
        a = h[0];
        var g = h[1], h = h[2];
        a += Math.round((b - a) * c);
        g += Math.round((f - g) * c);
        h += Math.round((d - h) * c);
        return"rgb(" + a + "," + g + "," + h + ")"
    };
    e.hex2RGB = function (a) {
        return[parseInt(a.substring(1, 3), 16), parseInt(a.substring(3, 5), 16), parseInt(a.substring(5, 7), 16)]
    };
    e.processDescriptionWindow =
        function (a, b) {
            isNaN(b.descriptionWindowX) && (b.descriptionWindowX = a.descriptionWindowX);
            isNaN(b.descriptionWindowY) && (b.descriptionWindowY = a.descriptionWindowY);
            isNaN(b.descriptionWindowLeft) && (b.descriptionWindowLeft = a.descriptionWindowLeft);
            isNaN(b.descriptionWindowRight) && (b.descriptionWindowRight = a.descriptionWindowRight);
            isNaN(b.descriptionWindowTop) && (b.descriptionWindowTop = a.descriptionWindowTop);
            isNaN(b.descriptionWindowBottom) && (b.descriptionWindowBottom = a.descriptionWindowBottom);
            isNaN(b.descriptionWindowWidth) &&
            (b.descriptionWindowWidth = a.descriptionWindowWidth);
            isNaN(b.descriptionWindowHeight) && (b.descriptionWindowHeight = a.descriptionWindowHeight)
        };
    e.normalizePath = function (a) {
        for (var b = "", c = e.parsePath(a.getAttribute("d")), d, f, h = Infinity, g = -Infinity, k = Infinity, l = -Infinity, m = 0; m < c.length; m++) {
            var n = c[m], p = n.letter, r = n.x, n = n.y;
            "h" == p && (p = "L", r += d, n = f);
            "H" == p && (p = "L", n = f);
            "v" == p && (p = "L", r = d, n += f);
            "V" == p && (p = "L", r = d);
            if ("m" === p || "l" === p)p = p.toUpperCase(), r += d, n += f;
            r = e.roundTo(r, 3);
            n = e.roundTo(n, 3);
            d = r;
            f = n;
            r > g && (g = r);
            r < h && (h = r);
            n > l && (l = n);
            n < k && (k = n);
            b = "z" == p.toLowerCase() ? b + "Z " : b + (p + " " + r + " " + n + " ")
        }
        a.setAttribute("d", b);
        return{minX: h, maxX: g, minY: k, maxY: l}
    };
    e.mercatorLatitudeToRadians = function (a) {
        return Math.log(Math.tan(Math.PI / 4 + e.degreesToRadians(a) / 2))
    };
    e.parsePath = function (a) {
        a = a.match(/([MmLlHhVvZz]{1}[0-9.,\-\s]*)/g);
        for (var b = [], c = 0; c < a.length; c++) {
            var d = a[c].match(/([MmLlHhVvZz]{1})|([0-9.\-]+)/g), e = {letter: d[0]};
            switch (d[0]) {
                case "Z":
                case "Z":
                case "z":
                    break;
                case "V":
                case "v":
                    e.y = Number(d[1]);
                    break;
                case "H":
                case "h":
                    e.x = Number(d[1]);
                    break;
                default:
                    e.x = Number(d[1]), e.y = Number(d[2])
            }
            b.push(e)
        }
        return b
    };
    e.acos = function (a) {
        return 1 < a ? 0 : -1 > a ? Math.PI : Math.acos(a)
    };
    e.asin = function (a) {
        return 1 < a ? Math.PI / 2 : -1 > a ? -Math.PI / 2 : Math.asin(a)
    };
    e.sinci = function (a) {
        return a ? a / Math.sin(a) : 1
    };
    e.asqrt = function (a) {
        return 0 < a ? Math.sqrt(a) : 0
    };
    e.winkel3 = function (a, b) {
        var c = e.aitoff(a, b);
        return[(c[0] + a / Math.PI * 2) / 2, (c[1] + b) / 2]
    };
    e.winkel3.invert = function (a, b) {
        var c = a, d = b, f = 25, h = Math.PI / 2;
        do var g = Math.cos(d), k = Math.sin(d),
            l = Math.sin(2 * d), m = k * k, n = g * g, p = Math.sin(c), r = Math.cos(c / 2), t = Math.sin(c / 2), q = t * t, u = 1 - n * r * r, C = u ? e.acos(g * r) * Math.sqrt(v = 1 / u) : v = 0, v, u = .5 * (2 * C * g * t + c / h) - a, D = .5 * (C * k + d) - b, y = .5 * v * (n * q + C * g * r * m) + .5 / h, E = v * (p * l / 4 - C * k * t), k = .125 * v * (l * t - C * k * n * p), m = .5 * v * (m * r + C * q * g) + .5, g = E * k - m * y, E = (D * E - u * m) / g, u = (u * k - D * y) / g, c = c - E, d = d - u; while ((1E-6 < Math.abs(E) || 1E-6 < Math.abs(u)) && 0 < --f);
        return[c, d]
    };
    e.aitoff = function (a, b) {
        var c = Math.cos(b), d = e.sinci(e.acos(c * Math.cos(a /= 2)));
        return[2 * c * Math.sin(a) * d, Math.sin(b) * d]
    };
    e.orthographic =
        function (a, b) {
            return[Math.cos(b) * Math.sin(a), Math.sin(b)]
        };
    e.equirectangular = function (a, b) {
        return[a, b]
    };
    e.equirectangular.invert = function (a, b) {
        return[a, b]
    };
    e.eckert5 = function (a, b) {
        var c = Math.PI;
        return[a * (1 + Math.cos(b)) / Math.sqrt(2 + c), 2 * b / Math.sqrt(2 + c)]
    };
    e.eckert5.invert = function (a, b) {
        var c = Math.sqrt(2 + Math.PI), d = b * c / 2;
        return[c * a / (1 + Math.cos(d)), d]
    };
    e.eckert6 = function (a, b) {
        for (var c = Math.PI, d = (1 + c / 2) * Math.sin(b), e = 0, h = Infinity; 10 > e && 1E-5 < Math.abs(h); e++)b -= h = (b + Math.sin(b) - d) / (1 + Math.cos(b));
        d = Math.sqrt(2 +
            c);
        return[a * (1 + Math.cos(b)) / d, 2 * b / d]
    };
    e.eckert6.invert = function (a, b) {
        var c = 1 + Math.PI / 2, d = Math.sqrt(c / 2);
        return[2 * a * d / (1 + Math.cos(b *= d)), e.asin((b + Math.sin(b)) / c)]
    };
    e.mercator = function (a, b) {
        b >= Math.PI / 2 - .02 && (b = Math.PI / 2 - .02);
        b <= -Math.PI / 2 + .02 && (b = -Math.PI / 2 + .02);
        return[a, Math.log(Math.tan(Math.PI / 4 + b / 2))]
    };
    e.mercator.invert = function (a, b) {
        return[a, 2 * Math.atan(Math.exp(b)) - Math.PI / 2]
    };
    e.miller = function (a, b) {
        return[a, 1.25 * Math.log(Math.tan(Math.PI / 4 + .4 * b))]
    };
    e.miller.invert = function (a, b) {
        return[a, 2.5 *
            Math.atan(Math.exp(.8 * b)) - .625 * Math.PI]
    };
    e.eckert3 = function (a, b) {
        var c = Math.PI, d = Math.sqrt(c * (4 + c));
        return[2 / d * a * (1 + Math.sqrt(1 - 4 * b * b / (c * c))), 4 / d * b]
    };
    e.eckert3.invert = function (a, b) {
        var c = Math.PI, d = Math.sqrt(c * (4 + c)) / 2;
        return[a * d / (1 + e.asqrt(1 - b * b * (4 + c) / (4 * c))), b * d / 2]
    }
})();
(function () {
    var e = window.AmCharts;
    e.MapData = e.Class({inherits: e.MapObject, construct: function () {
        this.cname = "MapData";
        e.MapData.base.construct.call(this);
        this.projection = "mercator";
        this.topLatitude = 90;
        this.bottomLatitude = -90;
        this.leftLongitude = -180;
        this.rightLongitude = 180;
        this.zoomLevel = 1;
        this.getAreasFromMap = !1
    }})
})();
(function () {
    var e = window.AmCharts;
    e.DescriptionWindow = e.Class({construct: function () {
    }, show: function (a, b, c, d) {
        var e = this;
        e.chart = a;
        var h = document.createElement("div");
        h.style.position = "absolute";
        var g = a.classNamePrefix + "-description-";
        h.className = "ammapDescriptionWindow " + g + "div";
        e.div = h;
        b.appendChild(h);
        var k = ".gif";
        a.svgIcons && (k = ".svg");
        var l = document.createElement("img");
        l.className = "ammapDescriptionWindowCloseButton " + g + "close-img";
        l.src = a.pathToImages + "xIcon" + k;
        l.style.cssFloat = "right";
        l.style.cursor =
            "pointer";
        l.onclick = function () {
            e.close()
        };
        l.onmouseover = function () {
            l.src = a.pathToImages + "xIconH" + k
        };
        l.onmouseout = function () {
            l.src = a.pathToImages + "xIcon" + k
        };
        h.appendChild(l);
        b = document.createElement("div");
        b.className = "ammapDescriptionTitle " + g + "title-div";
        b.onmousedown = function () {
            e.div.style.zIndex = 1E3
        };
        h.appendChild(b);
        b.innerHTML = d;
        d = b.offsetHeight;
        b = document.createElement("div");
        b.className = "ammapDescriptionText " + g + "text-div";
        b.style.maxHeight = e.maxHeight - d - 20 + "px";
        h.appendChild(b);
        b.innerHTML =
            c
    }, close: function () {
        try {
            this.div.parentNode.removeChild(this.div), this.chart.fireClosed()
        } catch (a) {
        }
    }})
})();
(function () {
    var e = window.AmCharts;
    e.ValueLegend = e.Class({construct: function (a) {
        this.cname = "ValueLegend";
        this.enabled = !0;
        this.showAsGradient = !1;
        this.minValue = 0;
        this.height = 12;
        this.width = 200;
        this.bottom = this.left = 10;
        this.borderColor = "#FFFFFF";
        this.borderAlpha = this.borderThickness = 1;
        this.color = "#000000";
        this.fontSize = 11;
        e.applyTheme(this, a, this.cname)
    }, init: function (a, b) {
        if (this.enabled) {
            var c = a.areasSettings.color, d = a.areasSettings.colorSolid, f = a.colorSteps;
            e.remove(this.set);
            var h = b.set();
            this.set =
                h;
            e.setCN(a, h, "value-legend");
            var g = 0, k = this.minValue, l = this.fontSize, m = a.fontFamily, n = this.color, p = {precision: a.precision, decimalSeparator: a.decimalSeparator, thousandsSeparator: a.thousandsSeparator};
            void 0 == k && (k = e.formatNumber(a.minValueReal, p));
            void 0 !== k && (g = e.text(b, k, n, m, l, "left"), g.translate(0, l / 2 - 1), e.setCN(a, g, "value-legend-min-label"), h.push(g), g = g.getBBox().height);
            k = this.maxValue;
            void 0 === k && (k = e.formatNumber(a.maxValueReal, p));
            void 0 !== k && (g = e.text(b, k, n, m, l, "right"), g.translate(this.width,
                l / 2 - 1), e.setCN(a, g, "value-legend-max-label"), h.push(g), g = g.getBBox().height);
            if (this.showAsGradient)c = e.rect(b, this.width, this.height, [c, d], 1, this.borderThickness, this.borderColor, 1, 0, 0), e.setCN(a, c, "value-legend-gradient"), c.translate(0, g), h.push(c); else for (l = this.width / f, m = 0; m < f; m++)n = e.getColorFade(c, d, 1 * m / (f - 1)), n = e.rect(b, l, this.height, n, 1, this.borderThickness, this.borderColor, 1), e.setCN(a, n, "value-legend-color"), e.setCN(a, n, "value-legend-color-" + m), n.translate(l * m, g), h.push(n);
            d = c = 0;
            f = h.getBBox();
            g = a.getY(this.bottom, !0);
            l = a.getY(this.top);
            m = a.getX(this.right, !0);
            n = a.getX(this.left);
            isNaN(l) || (c = l);
            isNaN(g) || (c = g - f.height);
            isNaN(n) || (d = n);
            isNaN(m) || (d = m - f.width);
            h.translate(d, c)
        } else e.remove(this.set)
    }})
})();
(function () {
    var e = window.AmCharts;
    e.ObjectList = e.Class({construct: function (a) {
        this.divId = a
    }, init: function (a) {
        this.chart = a;
        var b = this.divId;
        this.container && (b = this.container);
        this.div = "object" != typeof b ? document.getElementById(b) : b;
        b = document.createElement("div");
        b.className = "ammapObjectList " + a.classNamePrefix + "-object-list-div";
        this.div.appendChild(b);
        this.addObjects(a.dataProvider, b)
    }, addObjects: function (a, b) {
        var c = this.chart, d = document.createElement("ul");
        d.className = c.classNamePrefix + "-object-list-ul";
        var e;
        if (a.areas)for (e = 0; e < a.areas.length; e++) {
            var h = a.areas[e];
            void 0 === h.showInList && (h.showInList = c.showAreasInList);
            this.addObject(h, d)
        }
        if (a.images)for (e = 0; e < a.images.length; e++)h = a.images[e], void 0 === h.showInList && (h.showInList = c.showImagesInList), this.addObject(h, d);
        if (a.lines)for (e = 0; e < a.lines.length; e++)h = a.lines[e], void 0 === h.showInList && (h.showInList = c.showLinesInList), this.addObject(h, d);
        0 < d.childNodes.length && b.appendChild(d)
    }, addObject: function (a, b) {
        var c = this;
        if (a.showInList && void 0 !==
            a.title) {
            var d = c.chart, e = document.createElement("li");
            e.className = d.classNamePrefix + "-object-list-li";
            var h = a.titleTr;
            h || (h = a.title);
            var h = document.createTextNode(h), g = document.createElement("a");
            g.className = d.classNamePrefix + "-object-list-a";
            g.appendChild(h);
            e.appendChild(g);
            b.appendChild(e);
            this.addObjects(a, e);
            g.onmouseover = function () {
                c.chart.rollOverMapObject(a, !1)
            };
            g.onmouseout = function () {
                c.chart.rollOutMapObject(a)
            };
            g.onclick = function () {
                c.chart.clickMapObject(a)
            }
        }
    }})
})();