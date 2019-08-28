"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

console.clear();

var TWOPI = Math.PI * 2;

function distance(x1, y1, x2, y2) {
    var dx = x1 - x2;
    var dy = y1 - y2;
    return Math.sqrt(dx * dx + dy * dy);
}

var gravity = 0.5;

// VNode class

var VNode = function () {
    function VNode(node) {
        _classCallCheck(this, VNode);

        this.x = node.x || 0;
        this.y = node.y || 0;
        this.oldX = this.x;
        this.oldY = this.y;
        this.w = node.w || 2;
        this.angle = node.angle || 0;
        this.gravity = node.gravity || gravity;
        this.mass = node.mass || 1.0;

        this.color = node.color;
        this.letter = node.letter;

        this.pointerMove = node.pointerMove;
        this.fixed = node.fixed;
    }
    // verlet integration


    _createClass(VNode, [{
        key: 'integrate',
        value: function integrate(pointer) {

            if (this.lock && (!this.lockX || !this.lockY)) {
                this.lockX = this.x;
                this.lockY = this.y;
            }

            if (this.pointerMove && pointer && distance(this.x, this.y, pointer.x, pointer.y) < this.w + pointer.w) {
                this.x += (pointer.x - this.x) / (this.mass * 18);
                this.y += (pointer.y - this.y) / (this.mass * 18);
            } else if (this.lock) {
                this.x += (this.lockX - this.x) * this.lock;
                this.y += (this.lockY - this.y) * this.lock;
            }

            if (!this.fixed) {
                var x = this.x;
                var y = this.y;
                this.x += this.x - this.oldX;
                this.y += this.y - this.oldY + this.gravity;
                this.oldX = x;
                this.oldY = y;
            }
        }
    }, {
        key: 'set',
        value: function set(x, y) {
            this.oldX = this.x = x;
            this.oldY = this.y = y;
        }
        // draw node

    }, {
        key: 'draw',
        value: function draw(ctx) {
            if (!this.color) {
                return;
            }
            // ctx.globalAlpha = 0.8;
            ctx.translate(this.x, this.y);
            ctx.rotate(this.angle);
            ctx.fillStyle = this.color;

            ctx.beginPath();
            if (this.letter) {
                ctx.globalAlpha = 1;
                ctx.rotate(Math.PI / 2);

                ctx.rect(-7, 0, 14, 1);

                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.font = 'bold 75px "Bebas Neue", monospace';
                ctx.fillStyle = '#000';
                ctx.fillText(this.letter, 0, this.w * .25 + 4);

                ctx.fillStyle = this.color;
                ctx.fillText(this.letter, 0, this.w * .25);
            } else {
                ctx.globalAlpha = 0.2;
                ctx.rect(-this.w, -this.w, this.w * 2, this.w * 2);
                // ctx.arc(this.x, this.y, this.w, 0, 2 * Math.PI);
            }
            ctx.closePath();
            ctx.fill();
            ctx.setTransform(1, 0, 0, 1, 0, 0);
        }
    }]);

    return VNode;
}();
// constraint class


var Constraint = function () {
    function Constraint(n0, n1, stiffness) {
        _classCallCheck(this, Constraint);

        this.n0 = n0;
        this.n1 = n1;
        this.dist = distance(n0.x, n0.y, n1.x, n1.y);
        this.stiffness = stiffness || 0.5;
        this.firstRun = true;
    }
    // solve constraint


    _createClass(Constraint, [{
        key: 'solve',
        value: function solve() {
            var dx = this.n0.x - this.n1.x;
            var dy = this.n0.y - this.n1.y;

            var newAngle = Math.atan2(dy, dx);
            this.n1.angle = newAngle;

            var currentDist = distance(this.n0.x, this.n0.y, this.n1.x, this.n1.y);
            var delta = this.stiffness * (currentDist - this.dist) / currentDist;
            dx *= delta;
            dy *= delta;

            if (this.firstRun) {
                this.firstRun = false;
                if (!this.n1.fixed) {
                    this.n1.x += dx;
                    this.n1.y += dy;
                }
                if (!this.n0.fixed) {
                    this.n0.x -= dx;
                    this.n0.y -= dy;
                }
                return;
            }

            var m1 = this.n0.mass + this.n1.mass;
            var m2 = this.n0.mass / m1;
            m1 = this.n1.mass / m1;

            if (!this.n1.fixed) {
                this.n1.x += dx * m2;
                this.n1.y += dy * m2;
            }
            if (!this.n0.fixed) {
                this.n0.x -= dx * m1;
                this.n0.y -= dy * m1;
            }
        }
        // draw constraint

    }, {
        key: 'draw',
        value: function draw(ctx) {
            ctx.globalAlpha = 0.9;
            ctx.beginPath();
            ctx.moveTo(this.n0.x, this.n0.y);
            ctx.lineTo(this.n1.x, this.n1.y);
            ctx.strokeStyle = "#fff";
            ctx.stroke();
        }
    }]);

    return Constraint;
}();

var Rope = function () {
    function Rope(rope) {
        _classCallCheck(this, Rope);

        var x = rope.x,
            y = rope.y,
            length = rope.length,
            points = rope.points,
            vertical = rope.vertical,
            fixedEnds = rope.fixedEnds,
            startNode = rope.startNode,
            letter = rope.letter,
            endNode = rope.endNode,
            stiffness = rope.stiffness,
            constrain = rope.constrain,
            gravity = rope.gravity,
            pointerMove = rope.pointerMove;


        this.stiffness = stiffness || 1;
        this.nodes = [];
        this.constraints = [];
        if (letter === ' ') {
            return this;
        }

        var dist = length / points;

        for (var i = 0, _last = points - 1; i < points; i++) {

            var size = letter && i === _last ? 15 : 2;
            var spacing = dist * i + size;
            var node = new VNode({
                w: size,
                mass: .1, //(i === last ? .5 : .1),
                fixed: fixedEnds && (i === 0 || i === _last)
            });

            node = i === 0 && startNode || i === _last && endNode || node;

            node.gravity = gravity;
            //node.pointerMove = pointerMove;

            if (i === _last && letter) {
                node.letter = letter;
                node.color = '#FFF';
                node.pointerMove = true;
            }

            node.oldX = node.x = x + (!vertical ? spacing : 0);
            node.oldY = node.y = y + (vertical ? spacing : 0);

            this.nodes.push(node);
        }

        constrain ? this.makeConstraints() : null;
    }

    _createClass(Rope, [{
        key: 'makeConstraints',
        value: function makeConstraints() {
            for (var i = 1; i < this.nodes.length; i++) {
                this.constraints.push(new Constraint(this.nodes[i - 1], this.nodes[i], this.stiffness));
            }
        }
    }, {
        key: 'run',
        value: function run(pointer) {
            // integration
            var _iteratorNormalCompletion = true;
            var _didIteratorError = false;
            var _iteratorError = undefined;

            try {
                for (var _iterator = this.nodes[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                    var n = _step.value;

                    n.integrate(pointer);
                }
                // solve constraints
            } catch (err) {
                _didIteratorError = true;
                _iteratorError = err;
            } finally {
                try {
                    if (!_iteratorNormalCompletion && _iterator.return) {
                        _iterator.return();
                    }
                } finally {
                    if (_didIteratorError) {
                        throw _iteratorError;
                    }
                }
            }

            for (var i = 0; i < 5; i++) {
                var _iteratorNormalCompletion2 = true;
                var _didIteratorError2 = false;
                var _iteratorError2 = undefined;

                try {
                    for (var _iterator2 = this.constraints[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
                        var _n = _step2.value;

                        _n.solve();
                    }
                } catch (err) {
                    _didIteratorError2 = true;
                    _iteratorError2 = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion2 && _iterator2.return) {
                            _iterator2.return();
                        }
                    } finally {
                        if (_didIteratorError2) {
                            throw _iteratorError2;
                        }
                    }
                }
            }
        }

        // draw(ctx) {
        //   // draw constraints
        //   this.constraints.forEach(n => {
        //     n.draw(ctx);
        //   });
        //   // draw nodes
        //   this.nodes.forEach(n => {
        //     n.draw(ctx);
        //   })
        // }

    }, {
        key: 'draw',
        value: function draw(ctx) {

            var vertices = Array.from(this.constraints).reduce(function (p, c, i, a) {
                p.push(c.n0);
                if (i == a.length - 1) p.push(c.n1);
                return p;
            }, []);

            var h = function h(x, y) {
                return Math.sqrt(x * x + y * y);
            };
            var tension = 0.5;

            if (!vertices.length) return;

            var controls = vertices.map(function () {
                return null;
            });
            for (var i = 1; i < vertices.length - 1; ++i) {
                var previous = vertices[i - 1];
                var current = vertices[i];
                var next = vertices[i + 1];

                var rdx = next.x - previous.x,
                    rdy = next.y - previous.y,
                    rd = h(rdx, rdy),
                    dx = rdx / rd,
                    dy = rdy / rd;

                var dp = h(current.x - previous.x, current.y - previous.y),
                    dn = h(current.x - next.x, current.y - next.y);

                var cpx = current.x - dx * dp * tension,
                    cpy = current.y - dy * dp * tension,
                    cnx = current.x + dx * dn * tension,
                    cny = current.y + dy * dn * tension;

                controls[i] = {
                    cp: {
                        x: cpx,
                        y: cpy
                    },
                    cn: {
                        x: cnx,
                        y: cny
                    }
                };
            }

            controls[0] = {
                cn: {
                    x: (vertices[0].x + controls[1].cp.x) / 2,
                    y: (vertices[0].y + controls[1].cp.y) / 2
                }
            };

            controls[vertices.length - 1] = {
                cp: {
                    x: (vertices[vertices.length - 1].x + controls[vertices.length - 2].cn.x) / 2,
                    y: (vertices[vertices.length - 1].y + controls[vertices.length - 2].cn.y) / 2
                }
            };

            // Draw vertices & control points
            // ctx.fillStyle = 'blue';
            // ctx.fillRect(vertices[0].x, vertices[0].y, 4, 4);
            // for (let i = 1; i < vertices.length; ++i)
            // {
            // 	const v = vertices[i];
            // 	const ca = controls[i - 1];
            // 	const cb = controls[i];
            // 	ctx.fillStyle = 'blue';
            // 	ctx.fillRect(v.x, v.y, 4, 4);
            // 	ctx.fillStyle = 'green';
            // 	ctx.fillRect(ca.cn.x, ca.cn.y, 4, 4);
            // 	ctx.fillRect(cb.cp.x, cb.cp.y, 4, 4);
            // }

            ctx.globalAlpha = 0.9;
            ctx.beginPath();
            ctx.moveTo(vertices[0].x, vertices[0].y);
            for (var _i = 1; _i < vertices.length; ++_i) {
                var v = vertices[_i];
                var ca = controls[_i - 1];
                var cb = controls[_i];

                ctx.bezierCurveTo(ca.cn.x, ca.cn.y, cb.cp.x, cb.cp.y, v.x, v.y);
            }
            ctx.strokeStyle = 'white';
            ctx.stroke();
            ctx.closePath();

            // draw nodes
            this.nodes.forEach(function (n) {
                n.draw(ctx);
            });
        }
    }]);

    return Rope;
}();

// Pointer class


var Pointer = function (_VNode) {
    _inherits(Pointer, _VNode);

    function Pointer(canvas) {
        _classCallCheck(this, Pointer);

        var _this = _possibleConstructorReturn(this, (Pointer.__proto__ || Object.getPrototypeOf(Pointer)).call(this, {
            x: 0,
            y: 0,
            w: 8,
            color: '#F00',
            fixed: true
        }));

        _this.elem = canvas;
        canvas.addEventListener("mousemove", function (e) {
            return _this.move(e);
        }, false);
        canvas.addEventListener("touchmove", function (e) {
            return _this.move(e);
        }, false);
        return _this;
    }

    _createClass(Pointer, [{
        key: 'move',
        value: function move(e) {
            var touchMode = e.targetTouches;
            var pointer = e;
            if (touchMode) {
                e.preventDefault();
                pointer = touchMode[0];
            }
            var rect = this.elem.getBoundingClientRect();
            var cw = this.elem.width;
            var ch = this.elem.height;

            // get the scale based on actual width;
            var sx = cw / this.elem.offsetWidth;
            var sy = ch / this.elem.offsetHeight;

            this.x = (pointer.clientX - rect.left) * sx;
            this.y = (pointer.clientY - rect.top) * sy;
        }
    }]);

    return Pointer;
}(VNode);

var Scene = function () {
    function Scene(canvas) {
        _classCallCheck(this, Scene);

        this.draw = true;
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');

        this.nodes = new Set();
        this.constraints = new Set();
        this.ropes = [];

        this.pointer = new Pointer(canvas);
        this.nodes.add(this.pointer);

        this.run = this.run.bind(this);
        this.addRope = this.addRope.bind(this);
        this.add = this.add.bind(this);
    }

    // animation loop


    _createClass(Scene, [{
        key: 'run',
        value: function run() {
            var _this2 = this;

            // if (!canvas.isConnected) {
            //   return;
            // }
            requestAnimationFrame(this.run);
            // clear screen
            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

            this.ropes.forEach(function (rope) {
                rope.run(_this2.pointer);
            });

            this.ropes.forEach(function (rope) {
                rope.draw(_this2.ctx);
            });

            // // integration
            // for (let n of nodes) {
            //   n.integrate(pointer);
            // }
            // solve constraints
            // for (let i = 0; i < 4; i++) {
            //   for (let n of constraints) {
            //     n.solve();
            //   }
            // }
            // // draw constraints
            // for (let n of constraints) {
            //   n.draw(ctx);
            // }
            // draw nodes
            var _iteratorNormalCompletion3 = true;
            var _didIteratorError3 = false;
            var _iteratorError3 = undefined;

            try {
                for (var _iterator3 = this.nodes[Symbol.iterator](), _step3; !(_iteratorNormalCompletion3 = (_step3 = _iterator3.next()).done); _iteratorNormalCompletion3 = true) {
                    var n = _step3.value;

                    n.draw(this.ctx);
                }
            } catch (err) {
                _didIteratorError3 = true;
                _iteratorError3 = err;
            } finally {
                try {
                    if (!_iteratorNormalCompletion3 && _iterator3.return) {
                        _iterator3.return();
                    }
                } finally {
                    if (_didIteratorError3) {
                        throw _iteratorError3;
                    }
                }
            }
        }
    }, {
        key: 'addRope',
        value: function addRope(rope) {
            this.ropes.push(rope);
        }
    }, {
        key: 'add',
        value: function add(struct) {

            // load nodes
            for (var n in struct.nodes) {
                this.nodes.add(struct.nodes[n]);
                /*
                 const node = new Node(struct.nodes[n]);
                 struct.nodes[n].id = node;
                 nodes.add(node);
                 */
            }

            // load constraints
            for (var i = 0; i < struct.constraints.length; i++) {
                var c = struct.constraints[i];
                this.constraints.add(c);
                /*
                 new Constraint(
                 struct.nodes[c[0]].id,
                 struct.nodes[c[1]].id
                 )
                 );
                 */
            }
        }
    }]);

    return Scene;
}();

var scene = new Scene(document.querySelector('#canvas'));

scene.run();

// const pointer = new Pointer(canvas);

var phrase = '  CRMEB ';

var r = new Rope({
    x: scene.canvas.width * 0.15,
    y: 40,
    length: scene.canvas.width * 0.7,
    points: phrase.length,
    vertical: false,
    dangleEnd: false,
    fixedEnds: true,
    stiffness: 1.5,
    constrain: false,
    gravity: 0.1
});

var center = r.nodes.length / 2;

var ropes = r.nodes.map(function (n, i) {

    n.set(n.x, 60 + 80 * (1 - Math.abs((center - i) % center / center)));

    if (phrase[i] !== ' ') {

        //if ( i !== 0 && i !== r.nodes.length - 1 ) {
        return new Rope({
            startNode: n,
            x: n.x,
            y: n.y,
            length: 60,
            points: 4,
            letter: phrase[i],
            vertical: true,
            stiffness: 1, //2.5,,
            constrain: false,
            gravity: 0.5
        });
    }

    //}
});

var first = r.nodes[0];
var last = r.nodes[r.nodes.length - 1];

first.set(2, -2);
last.set(scene.canvas.width - 2, -2);

r.makeConstraints();

ropes = ropes;
scene.addRope(r);
ropes.filter(function (r) {
    return r;
}).forEach(function (r) {
    r.makeConstraints();
    scene.addRope(r);
});