# Font Scaling

This can be done by setting a value like `font-size: clamp( 30px, 5.8vw, 36px);`. The `clamp()` arguments will be different for each situation.

1. Determine the minimum (a) and maximum (b) width of the container that the text is in.
2. Determine the minimum (c) and maximum (d) font size that the text should have.
3. Calculate the midpoint (e) of the container widths: ( a + b ) / 2
4. Calculate the midpoint (f) of the font sizes: ( c + d ) / 2
5. Size your browser so that the container width is `e`.
6. Set `font-size: clamp( c, g, d );`, where `g` is a "magic number" ala https://css-tricks.com/fitting-text-to-a-container/
7. Tweak `g` until the font size in the browser equals `f`.

If the layout changes at a different breakpoint (e.g., switching from 1 column to 2), then repeat the process to get a new `font-size` value for that layout.
