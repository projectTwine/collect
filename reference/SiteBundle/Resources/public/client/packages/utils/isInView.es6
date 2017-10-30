const getViewportRect = () => ({
  width: window.innerWidth,
  height: window.innerHeight,
});

const getElementRect = (element) => {
  const rect = element.getBoundingClientRect();
  return {
    node: element,
    top: rect.top,
    right: rect.right,
    bottom: rect.bottom,
    left: rect.left,
    height: rect.height,
    width: rect.width,
  };
};

const rectsIntersect = (viewport, element) => (
  (element.node.offsetParent !== null) &&
  (element.top >= 0 && element.left >= 0) &&
  (viewport.height - element.top) > 0 &&
  (viewport.width - element.left) > 0 &&
  (viewport.height - (element.top + element.height)) > 0 &&
  (viewport.width - (element.left + element.width)) > 0
);

const isInView = element => (
  rectsIntersect(
    getViewportRect(),
    getElementRect(element),
  )
);

export default isInView;
