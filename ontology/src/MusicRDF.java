import com.hp.hpl.jena.query.Query;
import com.hp.hpl.jena.query.QueryExecution;
import com.hp.hpl.jena.query.QueryExecutionFactory;
import com.hp.hpl.jena.query.QueryFactory;
import com.hp.hpl.jena.query.ResultSet;
import com.hp.hpl.jena.query.ResultSetFormatter;
import com.hp.hpl.jena.rdf.model.InfModel;
import com.hp.hpl.jena.rdf.model.Model;
import com.hp.hpl.jena.rdf.model.ModelFactory;
import com.hp.hpl.jena.rdf.model.Property;
import com.hp.hpl.jena.rdf.model.Resource;
import com.hp.hpl.jena.rdf.model.Statement;
import com.hp.hpl.jena.rdf.model.StmtIterator;
import com.hp.hpl.jena.reasoner.Reasoner;
import com.hp.hpl.jena.reasoner.ReasonerRegistry;
import com.hp.hpl.jena.util.FileManager;
import com.hp.hpl.jena.util.PrintUtil;


public class MusicRDF {
	public static void printStatements(Model m, Resource s, Property p, Resource o) {
		StmtIterator i = m.listStatements(s,  p, o);
		while (i.hasNext()) {
			Statement stmt = i.nextStatement();
			System.out.println(" - " + PrintUtil.print(stmt));
		}
	}
	
	public static void executeQuery(String query_string, InfModel inf_model) {
		Query query = QueryFactory.create(query_string) ;
		QueryExecution qexec = QueryExecutionFactory.create(query, inf_model) ;
		try {
			ResultSet results = qexec.execSelect() ;
			ResultSetFormatter.out(System.out, results, query);
		} finally {
			qexec.close() ; 
		}
	}

	public static void main(String[] args) {	
		Model schema = FileManager.get().loadModel("music.owl");
		Model data = FileManager.get().loadModel("music.rdf");

		Reasoner reasoner = ReasonerRegistry.getOWLReasoner();
		reasoner.bindSchema(schema);

		InfModel inf_model = ModelFactory.createInfModel(reasoner, data);

		String prefixes ="PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>\n" +
				"PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>\n" +
				"PREFIX owl: <http://www.w3.org/2002/07/owl#>\n" +
				"PREFIX foaf: <http://xmlns.com/foaf/0.1/>\n" +
				"PREFIX dmusic: <http://www.semanticweb.org/dapi/ontologies/music#>\n"; 
		
		
		String query_string = prefixes +
				"select ?artist where { " +
				"?artist dmusic:worksWith dmusic:EMI ." +
				"{" +
				"?artist dmusic:hasGenre dmusic:Rock" +
				"} UNION " +
				"{" +
				"?artist dmusic:hasGenre ?genre ." +
				"?genre dmusic:subGenreOf dmusic:Rock}}";
		executeQuery(query_string, inf_model);
		
		query_string = prefixes +
				"select ?s  where {\n"+
					"?s rdf:type dmusic:Song.\n"+
					"?s dmusic:hasGenre ?g.\n"+
					"?g dmusic:subGenreOf dmusic:Rock.}";	
		executeQuery(query_string, inf_model);
		
	}

}
