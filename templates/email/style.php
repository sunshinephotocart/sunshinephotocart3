p, div, li, h1, h2, h3, h4, td, th, input, select, textarea, button { font: normal 18px/1.48 "Helvetica Neue", Helvetica, Arial, sans-serif; color: #000; }
body { padding: 0; margin: 0; }
a { color: #; text-decoration: none; }
a:hover { color: #cab796; }
h1, h2, h3 { line-height: 1.1; }

body { background-color: #FFF !important; }
#wrapper {
    background-color: #FFF !important;
    margin: 0;
    padding: 40px 1%;
    -webkit-text-size-adjust: none !important;
    width: 98%;
}

table {
  border-collapse: collapse;
  vertical-align: top;
}

#logo { font-size: 30px; color: #000; text-align: center; font-weight: normal; padding: 0 0 20px 0; }
#logo img { max-width: 600px; max-height: 100px; width: auto; height: auto; }

#header td { background: #efefef; padding: 15px 30px; text-align: center; }
#header td h1 { font-size: 24px; font-weight: normal; }

#main-wrapper { background: #FFF; border-radius: 4px; box-shadow: 0px 5px 10px 0px rgba(0,0,0,0.05); margin: 0 10px 10px 10px; border: 1px solid #EFEFEF; }

#content { padding: 30px 50px; }

#photo-list { margin: 0 0 40px 0; }
#photo-list td { text-align: center; padding: 0 3px 15px 3px; }
#photo-list td img { max-width: 100%; height: auto; }
#photo-list td span.image-name { color: #777; font-size: 12px; display: block; text-align: center; padding: 8px 0; }

a.button { display: inline-block; border: none; background: #000; font-size: 16px; color: #FFF; padding: 10px 25px; line-height: 1; border-radius: 50px; text-decoration: none; cursor: pointer; font-weight: 700; text-transform: uppercase; }

@media only screen and (max-width: 640px) {
    /* tablet-larger phone CSS styles go here */
    #main { width: 100% !important; }
    a.button { display: block; text-align: center; font-size: 15px; padding: 7px 15px; }
}
